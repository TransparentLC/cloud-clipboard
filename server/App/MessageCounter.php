<?php
// 消息数量计数器
$counter = new \Swoole\Atomic(0);
return $counter;