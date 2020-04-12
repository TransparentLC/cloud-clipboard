<?php
// 分片上传相关文件信息的内存表
// key 文件的唯一ID
// name 文件名
$table = new \Swoole\Table(1024);
$table->column('name', \Swoole\Table::TYPE_STRING, 256);
$table->create();
return $table;