<?php
namespace App\AbstractInterface;
abstract class HttpController {
    private $request;
    private $response;
    private $param;
    private $server;
    private $action;

    function __construct(\Swoole\Http\Request $request, \Swoole\Http\Response $response, $param, \Swoole\WebSocket\Server $server, $action = null) {
        $this->request = $request;
        $this->response = $response;
        $this->param = $param;
        $this->server = $server;
        $this->action = $action ?? 'index';

        $recurse_count = 0;
        try {
            while ($this->action !== null) {
                if ($recurse_count > 16) throw new \Exception('Recursion limit exceeded.');

                $this->action = $this->{$this->action}();
                $recurse_count++;
            }
        } catch (\Throwable $th) {
            $this->onException($th);
        }
        @$this->response->end();
    }

    /**
     * 获取Swoole的请求对象
     *
     * @return \Swoole\Http\Request
     */
    protected function request(): \Swoole\Http\Request {
        return $this->request;
    }

    /**
     * 获取Swoole的响应对象
     *
     * @return \Swoole\Http\Response
     */
    protected function response(): \Swoole\Http\Response {
        return $this->response;
    }

    /**
     * 获取FastRoute解析的参数列表
     *
     * @return mixed
     */
    protected function param() {
        return $this->param;
    }

    /**
     * 获取http服务
     *
     * @return \Swoole\WebSocket\Server
     */
    protected function server() {
        return $this->server;
    }

    /**
     * 控制器的主要逻辑
     *
     * @return void
     */
    function index() {}

    /**
     * 以JSON格式输出数据
     *
     * @param integer $status 状态码
     * @param array $result 输出的数据
     * @param string $msg 消息
     * @return void
     */
    function writeJson($status = 200, $result = [], $msg = '') {
        $this->response->header('Content-Type', 'application/json;charset=utf-8');
        $this->response->status($status);
        $this->response->end(json_encode([
            'code' => $status,
            'msg' => $msg ?? '',
            'result' => (object)($result ?? []),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    function onException(\Throwable $th) {
        $this->response->status(500);
        $this->response->header('Content-Type', 'text/html;charset=utf-8');
        $this->response->write(nl2br(join(PHP_EOL, [
            '<h1>Internal Server Error</h1>',
            $th->getMessage(),
            $th->getTraceAsString(),
        ])));
    }
}