<?php
require_once __DIR__ . '/vendor/autoload.php';

$config = require_once './App/Config.php';
$upload_table = require_once './App/UploadTable.php';

$server = new \Swoole\WebSocket\Server('0.0.0.0', $config->server->port);

$server->set([
    'http_compression' => false,
    'package_max_length' => 10485760,
    'document_root' => './static',
    'enable_static_handler' => true,
]);

$server->config = $config;
$server->upload_table = $upload_table;

$server->on('workerStart', function (\Swoole\WebSocket\Server $server) {
    // 所有的依赖都可以挂在这个对象里面
    $server->require = new \stdClass;
    $server->message_count = 0;

    $is_windows = in_array(strtoupper(PHP_OS), ['WINDOWS', 'WIN32', 'WINNT', 'CYGWIN']);
});

$server->on('workerStop', function (\Swoole\WebSocket\Server $server) {

});

$dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/server', 'ServerURI/index');
    $r->addRoute('POST', '/text', 'Text/index');
    $r->addRoute('GET', '/file/{uuid:[0-9a-f]{32}}', 'File/get');
    $r->addRoute('DELETE', '/file/{uuid:[0-9a-f]{32}}', 'File/delete');
    $r->addRoute('POST', '/upload', 'Upload/index');
    $r->addRoute('POST', '/upload/chunk/{uuid:[0-9a-f]{32}}', 'Upload/chunk');
    $r->addRoute('POST', '/upload/finish/{uuid:[0-9a-f]{32}}', 'Upload/finish');
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

    // $device = new \DeviceDetector\DeviceDetector($request->header['user-agent']);
    // $device->skipBotDetection();
    // $device->parse();
    // foreach ($server->connections as $fd) {
    //     if (!$server->isEstablished($fd)) continue;
    //     $server->push($fd, "{$device->getOs()['name']} {$device->getOs()['version']} ({$device->getClient()['name']} {$device->getClient()['version']})");
    // }
});

$server->on('close', function ($ser, $fd) {
    echo "client {$fd} closed\n";
});

$server->on('message', function (\Swoole\WebSocket\Server $server, \Swoole\WebSocket\Frame $frame) {
});

echo "Server listening on port {$config->server->port} ...\n";
$server->start();