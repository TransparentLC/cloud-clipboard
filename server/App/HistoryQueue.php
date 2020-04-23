<?php
namespace App;
class HistoryQueue {
    private $data_table;
    private $index_table;
    private $limit;
    const NULL_INDEX = "\x00\x00\x00\x00";

    /**
     * 新建一个队列用于保存历史消息
     *
     * @param int $limit 消息数量上限
     */
    function __construct(int $limit) {
        $this->limit = $limit;

        // 保存消息数据的内存表，使用了相当于链表的结构？
        // key 索引表中的index
        // content 这一段数据的内容
        // next 下一段数据的key，全是0的话就表示结束
        $this->data_table = new \Swoole\Table(2048);
        $this->data_table->column('content', \Swoole\Table::TYPE_STRING, 256);
        $this->data_table->column('next', \Swoole\Table::TYPE_STRING, 4);
        $this->data_table->create();

        // 保存消息索引的内存表，相当于链表的头结点？
        // key 消息ID
        // index data表中第一段消息的key，二进制格式
        $this->index_table = new \Swoole\Table(1024);
        $this->index_table->column('index', \Swoole\Table::TYPE_STRING, 4);
        $this->index_table->create();
    }

    /**
     * 获取一条指定ID的消息内容
     *
     * @param int $id 消息的ID
     * @return string
     */
    function get(int $id) {
        $index = $this->index_table->get($id, 'index');
        if ($index === false) return null;

        $result = '';
        while ($index !== $this::NULL_INDEX && ($data = $this->data_table->get($index)) !== false) {
            // $index_debug = bin2hex($index);
            // $next_debug = bin2hex($data['next']);
            // echo "Get #{$index_debug}:\nContent: {$data['content']}\nNext: {$next_debug}\n";

            $result .= $data['content'];
            $index = $data['next'];
        }
        return $result;
    }

    /**
     * 设定一条指定ID的消息内容
     *
     * @param int $id 消息的ID
     * @param string $content 消息的内容
     * @return void
     */
    function set(int $id, string $content) {
        $length = strlen($content);
        $insert = [];
        for ($i = 0; 256 * $i < $length; $i++) {
            do {
                $index = random_bytes(4);
            } while ($index === $this::NULL_INDEX || $this->data_table->exist(bin2hex($index)));
            $insert[] = [
                'index' => $index,
                'content' => substr($content, 256 * $i, 256),
            ];
        }
        $this->index_table->set($id, ['index' => $insert[0]['index']]);

        foreach ($insert as $key => $value) {
            // $index_debug = bin2hex($value['index']);
            // $next_debug = bin2hex(($key < count($insert) - 1) ? $insert[$key + 1]['index'] : $this::NULL_INDEX);
            // echo "Set {$index_debug} in #{$id}:\nContent: {$value['content']}\nNext: {$next_debug}\n";

            $this->data_table->set($value['index'], [
                'content' => $value['content'],
                'next' => ($key < count($insert) - 1) ? $insert[$key + 1]['index'] : $this::NULL_INDEX,
            ]);
        }

        if (($count = $this->index_table->count()) > $this->limit) {
            $delete_count = $count - $this->limit;
            $keys = $this->keys();
            // echo 'Keys: ' . join(', ', $keys) . " Delete Count: {$delete_count}\n";
            for ($i = 0; $i < $delete_count; $i++) {
                $this->del($keys[$i]);
            }
        }
    }

    /**
     * 删除一条指定ID的消息内容
     *
     * @param integer $id
     * @return void
     */
    function del(int $id) {
        $index = $this->index_table->get($id, 'index');
        $this->index_table->del($id);
        while ($index !== $this::NULL_INDEX && $index !== false) {
            // $index_debug = bin2hex($index);
            // echo "Delete: {$index_debug}\n";

            $next = $this->data_table->get($index, 'next');
            $this->data_table->del(bin2hex($index));
            $index = $next;
        }
    }

    /**
     * 返回所有已保存的ID
     *
     * @return mixed
     */
    function keys() {
        $keys = [];
        foreach ($this->index_table as $key => $value) {
            $keys[] = (int)$key;
        }
        sort($keys);
        return $keys;
    }
}

return new HistoryQueue($config->server->history);