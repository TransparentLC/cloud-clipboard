<?php
// 上传文件信息的内存表
// key 文件的UUID
// upload/expire_timestamp 文件上传/过期的时间戳

$table = new \Swoole\Table(1024);
$table->column('name', \Swoole\Table::TYPE_STRING, 256);
$table->column('size', \Swoole\Table::TYPE_INT, 8);
$table->column('upload_timestamp', \Swoole\Table::TYPE_INT, 8);
$table->column('expire_timestamp', \Swoole\Table::TYPE_INT, 8);
$table->create();

// 开始上传时设定当前时间为上传时间戳，过期时间戳设为0
// 上传完成则设定当前时间加上过期时间为过期时间戳
// 每隔一小时对文件表进行检查，出现以下情况则删除文件/不允许下载文件：
// 过期时间戳距离现在已有x秒（文件过期）
// 过期时间戳为0，上传时间戳距离现在已有x秒（上传过期）
\Swoole\Timer::tick(1800000, function () use ($table, $config) {
    // echo "Checking file status ...\n";
    // echo 'Time: ' . time() . "\n";
    $delete_uuid = [];
    foreach ($table as $uuid => $row) {
        // echo "Checking {$uuid} ...\n";
        // echo "Upload: {$row['upload_timestamp']} Expire: {$row['expire_timestamp']}\n";
        if (
            $row['expire_timestamp'] ? (time() > $row['expire_timestamp']) : (time() - $row['upload_timestamp'] > 60)
        ) {
            echo "File \"{$row['name']}\" " . ($row['expire_timestamp'] ? "expired.\n" : "failed to upload.\n");
            $delete_uuid[] = $uuid;
        }
    }
    foreach ($delete_uuid as $uuid) {
        $table->del($uuid);
        unlink("{$config->server->storage}/{$uuid}");
    }
});

return $table;