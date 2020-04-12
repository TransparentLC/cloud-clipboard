<?php
namespace App\HttpController;
class File extends \App\AbstractInterface\HttpController {
    function get() {
        $storage = $this->server()->config->file->storage;
        /** @var \Swoole\Table */
        $upload_table = $this->server()->upload_table;
        $uuid = $this->param()['uuid'];

        if ($upload_table->exist($uuid) && file_exists("{$storage}/{$uuid}")) {
            $this->response()->header('Content-Disposition', 'attachment;filename="' . rawurlencode($upload_table->get($uuid, 'name')) . '"');
            $this->response()->sendFile("{$storage}/{$uuid}");
        } else {
            $this->response()->status(404);
        }
    }

    function delete() {
        $storage = $this->server()->config->file->storage;
        /** @var \Swoole\Table */
        $upload_table = $this->server()->upload_table;
        $uuid = $this->param()['uuid'];

        if ($upload_table->del($uuid) && unlink("{$storage}/{$uuid}")) {
            $this->writeJson();
        } else {
            $this->writeJson(404, [], '删除失败');
        }
    }
}