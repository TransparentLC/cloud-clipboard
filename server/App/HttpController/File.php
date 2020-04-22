<?php
namespace App\HttpController;
class File extends \App\AbstractInterface\HttpController {
    function get() {
        $storage = $this->server()->config->server->storage;
        /** @var \Swoole\Table */
        $upload_table = $this->server()->upload_table;
        $uuid = $this->param()['uuid'];

        if ((time() < $upload_table->get($uuid, 'expire_timestamp')) && file_exists("{$storage}/{$uuid}")) {
            $this->response()->header('Content-Disposition', 'attachment;filename="' . rawurlencode($upload_table->get($uuid, 'name')) . '"');
            $this->response()->sendFile("{$storage}/{$uuid}");
        } else {
            $this->response()->status(404);
        }
    }

    function delete() {
        $storage = $this->server()->config->server->storage;
        /** @var \Swoole\Table */
        $upload_table = $this->server()->upload_table;
        $uuid = $this->param()['uuid'];

        if ($upload_table->del($uuid)) {
            if (unlink("{$storage}/{$uuid}")) {
                $this->writeJson();
            } else {
                $this->writeJson(400);
            }
        } else {
            $this->writeJson(404, [], '文件已过期或不存在');
        }
    }
}