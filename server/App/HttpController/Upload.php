<?php
namespace App\HttpController;
class Upload extends \App\AbstractInterface\HttpController {
    function index() {
        $storage = $this->server()->config->server->storage;
        /** @var \Swoole\Table */
        $upload_table = $this->server()->upload_table;

        do {
            $uuid = bin2hex(random_bytes(16));
        } while ($upload_table->exist($uuid));

        \Swoole\Timer::after(60000, function () use ($upload_table, $uuid, $storage) {
            if (file_exists("{$storage}/~{$uuid}")) {
                echo "Upload {$uuid} failed, remove temp files.\n";
                $upload_table->del($uuid);
                unlink("{$storage}/~{$uuid}");
            } else {
                echo "Upload {$uuid} success, set expire timer.\n";
                \Swoole\Timer::after(max(0, $this->server()->config->file->expire - 30000), function () use ($upload_table, $uuid, $storage) {
                    echo "Upload {$uuid} expired.\n";
                    $upload_table->del($uuid);
                    @unlink("{$storage}/{$uuid}");
                });
            }
        });

        $upload_table->set($uuid, [
            'name' => $this->request()->rawContent(),
            'size' => 0,
        ]);

        fclose(fopen("{$storage}/~{$uuid}", 'w'));

        $this->writeJson(200, ['uuid' => $uuid]);
    }

    function chunk() {
        $storage = $this->server()->config->server->storage;
        /** @var \Swoole\Table */
        $upload_table = $this->server()->upload_table;
        $chunk_length = strlen($this->request()->rawContent());
        $uuid = $this->param()['uuid'];

        if (!$upload_table->exist($uuid) || !file_exists("{$storage}/~{$uuid}")) {
            $this->writeJson(400, [], '无效的 UUID');
            return;
        } else if (!$chunk_length || $chunk_length > $this->server()->config->file->chunk) {
            $this->writeJson(413, [], "分片长度不能超过 {$this->server()->config->file->chunk} 字节");
            return;
        }

        $file_meta = $upload_table->get($uuid);

        if ($file_meta['size'] + $chunk_length > $this->server()->config->file->limit) {
            $this->writeJson(400, [], '文件大小已超过限制');
            return;
        }

        $fp = fopen("{$storage}/~{$uuid}", 'a');
        fwrite($fp, $this->request()->rawContent());
        fclose($fp);

        $file_meta['size'] += $chunk_length;
        $upload_table->set($uuid, $file_meta);

        $this->writeJson();
    }

    function finish() {
        $storage = $this->server()->config->server->storage;
        /** @var \Swoole\Table */
        $upload_table = $this->server()->upload_table;
        $uuid = $this->param()['uuid'];

        if (!$upload_table->exist($uuid) || !file_exists("{$storage}/~{$uuid}")) {
            $this->writeJson(400, [], '无效的 uuid');
            return;
        }

        rename("{$storage}/~{$uuid}", "{$storage}/{$uuid}");
        $broadcast = [
            'event' => 'receive',
            'data' => [
                'id' => $this->server()->message_count->add(),
                'type' => 'file',
                'name' => $upload_table->get($uuid, 'name'),
                'size' => filesize("{$storage}/{$uuid}"),
                'cache' => $uuid,
            ],
        ];
        $broadcast_json = json_encode($broadcast, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        foreach ($this->server()->connections as $fd) {
            if (!$this->server()->isEstablished($fd)) continue;
            $this->server()->push($fd, $broadcast_json);
        }

        $this->writeJson();
    }
}