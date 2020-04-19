<?php
// 读取设置，创建临时存储文件夹
$config = json_decode(file_get_contents('config.json'));
if (empty($config->server->storage)) exit('Please set a valid storage path in "config.json".');

// 检查分片上传大小限制
if ($config->file->chunk > 5242880) {
    $config->file->chunk = 5242880;
    echo "Upload chunk size is restricted to 5 MB.\n";
}

// 创建临时存储文件夹，或提示该文件夹内已有文件
if (is_dir($config->server->storage)) {
    if (count(scandir($config->server->storage)) !== 2) {
        echo "The storage path is not empty:\n";
        echo realpath($config->server->storage) . "\n";
    }
} else {
    mkdir($config->server->storage);
}

return $config;