<?php
namespace App\HttpController;
class File extends \App\AbstractInterface\HttpController {
    function index() {
        if (in_array($this->request()->server['request_method'], ['GET', 'POST', 'DELETE'])) {
            return strtolower($this->request()->server['request_method']);
        }
    }

    function get() {
        $path = $this->server()->config->file->storage . $this->param()['hash'];
        if (file_exists($path)) {
            $fp = fopen($path, 'r');
            $filename = str_replace(["\r", "\n"], '', fgets($fp));
            $offset = ftell($fp);
            fclose($fp);

            $this->response()->header('Content-Disposition', 'attachment; filename=' . urlencode($filename));
            $this->response()->sendFile($path, $offset);
        } else {
            $this->response()->status(404);
        }
    }

    function post() {
        // TODO
    }

    function delete() {
        $path = $this->server()->config->file->storage . $this->param()['hash'];
        if (unlink($path)) {
            $this->writeJson();
        } else {
            $this->writeJson(404, [], '删除失败');
        }
    }
}