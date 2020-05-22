<?php
// 读取设置
$config_path = realpath(IS_PHAR ? (dirname(Phar::running(false)) . '/config.json') : (__DIR__ . '/../config.json'));
if (file_exists($config_path)) {
    echo "Loading config file:\n";
    echo $config_path. "\n";
} else {
    echo "Config file not found:\n";
    echo $config_path. "\n";
    exit;
}

$config = json_decode(file_get_contents($config_path));

$config->server->storage = dirname($config_path) . '/.storage';

// 剪贴板的密码可以自定义
// 如果写true的话就随机生成六位数字
if (empty($config->server->auth)) {
    $config->server->auth = false;
} else if ($config->server->auth === true) {
    $config->server->auth = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
} else {
    $config->server->auth = (string)$config->server->auth;
}

// 检查分片上传大小限制
if ($config->file->chunk > 5242880) {
    $config->file->chunk = 5242880;
    echo "Upload chunk size is restricted to 5 MB.\n";
}

// 创建临时存储文件夹，或提示该文件夹内已有文件
if (is_dir($config->server->storage)) {
    if (count(scandir($config->server->storage)) !== 2) {
        echo "The storage path is not empty:\n";
        echo $config->server->storage . "\n";
    }
} else {
    mkdir($config->server->storage);
}

return $config;