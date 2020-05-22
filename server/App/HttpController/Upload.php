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

        $upload_table->set($uuid, [
            'name' => $this->request()->rawContent(),
            'size' => 0,
            'upload_timestamp' => time(),
            'expire_timestamp' => 0,
        ]);

        fclose(fopen("{$storage}/{$uuid}", 'w'));

        $this->writeJson(200, ['uuid' => $uuid]);
    }

    function chunk() {
        $storage = $this->server()->config->server->storage;
        /** @var \Swoole\Table */
        $upload_table = $this->server()->upload_table;
        $chunk_length = strlen($this->request()->rawContent());
        $uuid = $this->param()['uuid'];

        if ($upload_table->get($uuid, 'expire_timestamp') !== 0) {
            $this->writeJson(400, [], '无效的 UUID');
            return;
        } else if (!$chunk_length || $chunk_length > $this->server()->config->file->chunk) {
            $this->writeJson(413, [], "分片长度不能超过 {$this->server()->config->file->chunk} 字节");
            return;
        }

        $size = $upload_table->get($uuid, 'size');
        if ($size + $chunk_length > $this->server()->config->file->limit) {
            $this->writeJson(400, [], '文件大小已超过限制');
            return;
        }

        $fp = fopen("{$storage}/{$uuid}", 'a');
        fwrite($fp, $this->request()->rawContent());
        fclose($fp);

        $upload_table->set($uuid, ['size' => $size + $chunk_length]);

        $this->writeJson();
    }

    function finish() {
        $storage = $this->server()->config->server->storage;
        /** @var \Swoole\Table */
        $upload_table = $this->server()->upload_table;
        $uuid = $this->param()['uuid'];

        if ($upload_table->get($uuid, 'expire_timestamp') !== 0) {
            $this->writeJson(400, [], '无效的 UUID');
            return;
        }

        $id = $this->server()->message_count->add();
        $upload_table->set($uuid, ['expire_timestamp' => time() + $this->server()->config->file->expire]);
        $broadcast = [
            'event' => 'receive',
            'data' => [
                'id' => $id,
                'type' => 'file',
                'name' => $upload_table->get($uuid, 'name'),
                'size' => $upload_table->get($uuid, 'size'),
                'cache' => $uuid,
                'expire' => $upload_table->get($uuid, 'expire_timestamp'),
            ],
        ];

        try {
            // 32MB以上文件不生成缩略图
            if (filesize("{$storage}/{$uuid}") > 33554432) throw new \Exception();
            // 非图像文件不生成缩略图
            if (($imagesize = getimagesize("{$storage}/{$uuid}")) === false) throw new \Exception();

            $image = imagecreatefromstring(file_get_contents("{$storage}/{$uuid}"));
            list($width, $height, $type, $attr) = $imagesize;
            if (min($width, $height) > 64) {
                $ratio = 64 / min($width, $height);
                $image = imagescale($image, $width * $ratio, $height * $ratio, IMG_BICUBIC_FIXED);
            }
            imageinterlace($image, true);
            ob_start();
            imagejpeg($image, null, 70);
            $image_data = ob_get_clean();
            imagedestroy($image);
            $broadcast['data']['thumbnail'] = 'data:image/jpeg;base64,' . base64_encode($image_data);
        } catch (\Throwable $th) {}

        $broadcast_json = json_encode($broadcast, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->server()->history_queue->set($id, $broadcast_json);
        foreach ($this->server()->connections as $fd) {
            if (!$this->server()->isEstablished($fd)) continue;
            $this->server()->push($fd, $broadcast_json);
        }

        $this->writeJson();
    }
}