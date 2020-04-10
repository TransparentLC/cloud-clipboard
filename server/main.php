<?php
require_once __DIR__ . '/vendor/autoload.php';

$server = new \Swoole\WebSocket\Server('0.0.0.0', 9501);

$server->set([
    'http_compression' => false,
    'document_root' => './static',
    'enable_static_handler' => true,
]);

$server->on('workerStart', function (\Swoole\WebSocket\Server $server) {
    // 所有的依赖都可以挂在这个对象里面
    $server->require = new \stdClass;

    $is_windows = in_array(strtoupper(PHP_OS), ['WINDOWS', 'WIN32', 'WINNT', 'CYGWIN']);
});
$server->on('workerExit', function (\Swoole\WebSocket\Server $server) {

});

$dispatcher = FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/user/{id:\d+}', 'User/index');
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

$server->on('open', function (Swoole\WebSocket\Server $server, \Swoole\Http\Request $request) {
    echo "server: handshake success with fd{$request->fd}\n";
});

$server->on('close', function ($ser, $fd) {
    echo "client {$fd} closed\n";
});

$server->on('message', function (Swoole\WebSocket\Server $server, \Swoole\WebSocket\Frame $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $test = [
        'event' => 'receive',
        'data' => [
            'id' => mt_rand(10, 1000),
            'type' => 'text',
            'content' => 'akarin',
        ],
    ];
    $test = [
        'event' => 'receive',
        'data' => [
            'id' => mt_rand(10, 1000),
            'type' => 'file',
            'name' => 'akarin.jpg',
            'size' => mt_rand(100000, 500000),
            'cache' => bin2hex(random_bytes(16)),
        ],
    ];
    $server->push($frame->fd, json_encode($test));
});

$server->start();