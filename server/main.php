<?php
if (php_sapi_name() !== 'cli') exit('Use CLI to run this application.');
if (!extension_loaded('swoole')) exit('Swoole is not installed.');

require_once __DIR__ . '/vendor/autoload.php';

$config = require_once './App/Config.php';
$server = new \Swoole\WebSocket\Server('0.0.0.0', $config->server->port, SWOOLE_BASE);

$server->set([
    'package_max_length' => 5242880,
    'document_root' => './static',
    'enable_static_handler' => true,
]);

$server->config = $config;
$server->upload_table = require_once './App/UploadTable.php';
$server->device_table = require_once './App/DeviceTable.php';
$server->message_count = require_once './App/MessageCounter.php';
// 所有的依赖都可以挂在这个对象里面
// 现在好像还用不到
// $server->require = new \stdClass;

$server->on('workerStart', function (\Swoole\WebSocket\Server $server) {
    // 所有的数据库连接都要在这里创建
    // https://wiki.swoole.com/#/question/use?id=是否可以共用1个redis或mysql连接
});

$server->on('workerStop', function (\Swoole\WebSocket\Server $server) {
    // 数据库连接在这里关闭
});

$dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/server', 'ServerURI/index');
    $r->addRoute('POST', '/text', 'Text/index');
    $r->addRoute('GET', '/file/{uuid:[0-9a-f]{32}}', 'File/get');
    $r->addRoute('DELETE', '/file/{uuid:[0-9a-f]{32}}', 'File/delete');
    $r->addRoute('POST', '/upload', 'Upload/index');
    $r->addRoute('POST', '/upload/chunk/{uuid:[0-9a-f]{32}}', 'Upload/chunk');
    $r->addRoute('POST', '/upload/finish/{uuid:[0-9a-f]{32}}', 'Upload/finish');
    $r->addRoute('DELETE', '/revoke/{id:\d+}', 'Revoke/index');
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
    $server->push($request->fd, json_encode([
        'event' => 'config',
        'data' => [
            'text' => $server->config->text,
            'file' => $server->config->file,
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    foreach ($server->device_table as $fd => $row) {
        $server->push($request->fd, json_encode([
            'event' => 'connect',
            'data' => array_merge(['id' => $fd], $row),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    $device = new \DeviceDetector\DeviceDetector($request->header['user-agent']);
    $device->parse();
    if ($device->isBot()) {
        $device_meta = [
            'type' => 'bot',
            'device' => '',
            'os' => '',
            'browser' => '',
        ];
    } else {
        $device_meta = [
            'type' => trim($device->getDeviceName()),
            'device' => trim("{$device->getBrandName()} {$device->getModel()}"),
            'os' => trim("{$device->getOs()['name']} {$device->getOs()['version']}"),
            'browser' => trim("{$device->getClient()['name']} {$device->getClient()['version']}"),
        ];
    }
    $server->device_table->set($request->fd, $device_meta);

    $broadcast = [
        'event' => 'connect',
        'data' => array_merge(['id' => $request->fd], $device_meta),
    ];
    $broadcast_json = json_encode($broadcast, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    foreach ($server->connections as $fd) {
        if (!$server->isEstablished($fd)) continue;
        $server->push($fd, $broadcast_json);
    }
});

$server->on('close', function (\Swoole\WebSocket\Server $server, $fd) {
    if ($server->isEstablished($fd)) {
        $server->device_table->del($fd);
        $broadcast = [
            'event' => 'disconnect',
            'data' => ['id' => $fd],
        ];
        $broadcast_json = json_encode($broadcast, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        foreach ($server->connections as $fd) {
            if (!$server->isEstablished($fd)) continue;
            $server->push($fd, $broadcast_json);
        }
    }
});

$server->on('message', function (\Swoole\WebSocket\Server $server, \Swoole\WebSocket\Frame $frame) {
});

echo "Server listening on port {$config->server->port} ...\n";
$server->start();