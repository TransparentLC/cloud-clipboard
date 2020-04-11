<?php
require_once __DIR__ . '/vendor/autoload.php';

// 读取设置，创建临时存储文件夹
$config = json_decode(file_get_contents('config.json'));
if (empty($config->file->storage)) exit('Please set a valid storage path in "config.json".');
$config->file->storage = __DIR__ . $config->file->storage;

// 清空或创建临时存储文件夹
if (is_dir($config->file->storage)) {
    echo 'Press enter to CLEAR ALL FILES in storage path and continue:' . PHP_EOL;
    echo realpath($config->file->storage) . PHP_EOL;
    fgets(STDIN);

    // How do I recursively delete a directory and its entire contents (files + sub dirs) in PHP?
    // https://stackoverflow.com/questions/3338123/how-do-i-recursively-delete-a-directory-and-its-entire-contents-files-sub-dir#answer-3352564
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($config->file->storage, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $fileinfo) {
        ($fileinfo->isDir() ? 'rmdir' : 'unlink')($fileinfo->getRealPath());
    }
} else {
    mkdir($config->file->storage);
}

$server = new \Swoole\WebSocket\Server('0.0.0.0', $config->server->port);

$server->set([
    'http_compression' => false,
    'document_root' => './static',
    'enable_static_handler' => true,
]);

$server->on('workerStart', function (\Swoole\WebSocket\Server $server) use ($config) {
    // 所有的依赖都可以挂在这个对象里面
    $server->require = new \stdClass;
    $server->config = $config;
    $server->message_count = 0;

    $is_windows = in_array(strtoupper(PHP_OS), ['WINDOWS', 'WIN32', 'WINNT', 'CYGWIN']);
});

$server->on('workerStop', function (\Swoole\WebSocket\Server $server) {

});

$dispatcher = FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/server', 'ServerURI/index');
    $r->addRoute('POST', '/text', 'Text/index');
    $r->addRoute(['GET', 'POST', 'DELETE'], '/file[/{hash:[0-9a-f]{32}}]', 'File/index');
});

$server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use ($dispatcher, $server) {
    $route = $dispatcher->dispatch($request->server['request_method'], $request->server['request_uri']);
    switch ($route[0]) {
        case \FastRoute\Dispatcher::NOT_FOUND:
            $response->status(404);
            $response->end();
            break;
        case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            $response->status(405);
            $response->end();
            break;
        case \FastRoute\Dispatcher::FOUND:
            list($class, $action) = explode('/', $route[1]);
            $class = '\\App\\HttpController\\' . $class;
            $param = $route[2];
            new $class($request, $response, $param, $server, $action);
            break;
    }
});

$server->on('open', function (\Swoole\WebSocket\Server $server, \Swoole\Http\Request $request) {
    echo "server: handshake success with fd{$request->fd}\n";

    $device = new \DeviceDetector\DeviceDetector($request->header['user-agent']);
    $device->skipBotDetection();
    $device->parse();
    foreach ($server->connections as $fd) {
        if (!$server->isEstablished($fd)) continue;
        $server->push($fd, "{$device->getOs()['name']} {$device->getOs()['version']} ({$device->getClient()['name']} {$device->getClient()['version']})");
    }
});

$server->on('close', function ($ser, $fd) {
    echo "client {$fd} closed\n";
});

$server->on('message', function (\Swoole\WebSocket\Server $server, \Swoole\WebSocket\Frame $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    // $test = [
    //     'event' => 'receive',
    //     'data' => [
    //         'id' => mt_rand(10, 1000),
    //         'type' => 'text',
    //         'content' => 'akarin',
    //     ],
    // ];
    // $test = [
    //     'event' => 'receive',
    //     'data' => [
    //         'id' => mt_rand(10, 1000),
    //         'type' => 'file',
    //         'name' => 'akarin.jpg',
    //         'size' => mt_rand(100000, 500000),
    //         'cache' => bin2hex(random_bytes(16)),
    //     ],
    // ];
    // $server->push($frame->fd, json_encode($test));
});

echo "Server listening on port {$config->server->port} ...\n";
$server->start();