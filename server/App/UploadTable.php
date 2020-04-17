<?php
// 上传文件信息的内存表
// key 文件的唯一ID
// name 文件名
// size 已上传的文件大小
$table = new \Swoole\Table(1024);
$table->column('name', \Swoole\Table::TYPE_STRING, 256);
$table->column('size', \Swoole\Table::TYPE_INT, 8);
$table->create();
return $table;