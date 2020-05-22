<?php
namespace App\HttpController;
class ServerURI extends \App\AbstractInterface\HttpController {
    function index() {
        $this->response()->header('Content-Type', 'application/json;charset=utf-8');
        $this->response()->end(json_encode(
            [
                'server' => sprintf(
                    'ws%s://%s:%s/push',
                    $this->server()->config->server->wss ? 's' : '',
                    $this->server()->config->server->host,
                    $this->server()->config->server->port
                ),
                'auth' => (bool)$this->server()->config->server->auth,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}