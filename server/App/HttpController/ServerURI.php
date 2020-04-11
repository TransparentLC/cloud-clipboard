<?php
namespace App\HttpController;
class ServerURI extends \App\AbstractInterface\HttpController {
    function index() {
        $this->response()->header('Content-Type', 'text/plain');
        $this->response()->write(sprintf(
            'ws%s://%s:%s',
            $this->server()->config->server->wss ? 's' : '',
            $this->server()->config->server->host,
            $this->server()->config->server->port
        ));
    }
}