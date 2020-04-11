<?php
namespace App\HttpController;
class Text extends \App\AbstractInterface\HttpController {
    function index() {
        if (mb_strlen($this->request()->rawContent()) > $this->server()->config->text->limit) {
            $this->writeJson(400, [], "文本长度不能超过 {$this->server()->config->text->limit} 字节");
            return;
        }
        $this->writeJson();

        $this->server()->message_count++;
        $broadcast = json_encode([
            'event' => 'receive',
            'data' => [
                'id' => $this->server()->message_count,
                'type' => 'text',
                'content' => $this->request()->rawContent(),
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        foreach ($this->server()->connections as $fd) {
            if (!$this->server()->isEstablished($fd)) continue;
            $this->server()->push($fd, $broadcast);
        }
    }
}