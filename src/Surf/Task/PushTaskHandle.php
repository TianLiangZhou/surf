<?php
/**
 * 
 * 
 * 
 */

namespace Surf\Task;

use Surf\Cache\Driver\Redis;
use Surf\Server\RedisConstant;

class PushTaskHandle extends TaskHandle
{

    /**
     * 推送消息, 只有客户端是webSocket或者异步客户端才会收到消息, 不要在http server中使用推送
     * @return bool
     */
    public function execute(): bool
    {
        $content = $this->content;
        if (is_array($this->content)) {
            $from = $this->content['from'] ?? [];
            $connections = $from;
            if (is_string($from)) {
                $connections = [$from];
            }
            $content = $this->content['content'] ?? "\r\n";
        } else {
            if ($this->container->has('redis')) {
                /**
                 * @var $redis Redis|\Redis
                 */
                $redis = $this->container->get('redis');
                $connections = $redis->sMembers(RedisConstant::FULL_CONNECT_FD);
            } else {
                $connections = $this->server->connections;
            }
        }
        $method = 'send';
        if ($this->server instanceof \Swoole\WebSocket\Server) {
            $method = 'push';
        }
        foreach ($connections as $fd) {
            $this->server->$method($fd, $content);
        }
        return true;
    }
}