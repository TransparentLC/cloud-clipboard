<?php
namespace App\HttpController;
class Revoke extends \App\AbstractInterface\HttpController {
    function index() {
        if ($this->server()->message_count->get() < (int)$this->param()['id']) {
            $this->writeJson(400, [], '不存在的消息 ID');
            return;
        }
        $this->writeJson();

        $id = (int)$this->param()['id'];
        $this->server()->history_queue->del($id);
        $broadcast = [
            'event' => 'revoke',
            'data' => [
                'id' => $id,
            ],
        ];
        $broadcast_json = json_encode($broadcast, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        foreach ($this->server()->connections as $fd) {
            if (!$this->server()->isEstablished($fd)) continue;
            $this->server()->push($fd, $broadcast_json);
        }
    }
}