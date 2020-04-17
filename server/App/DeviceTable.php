<?php
// 已连接设备的内存表
// key fd
// type 参见https://github.com/matomo-org/device-detector/blob/master/Parser/Device/DeviceParserAbstract.php#L44
$table = new \Swoole\Table(1024);
$table->column('type', \Swoole\Table::TYPE_STRING, 16);
$table->column('device', \Swoole\Table::TYPE_STRING, 48);
$table->column('os', \Swoole\Table::TYPE_STRING, 48);
$table->column('browser', \Swoole\Table::TYPE_STRING, 48);
$table->create();
return $table;