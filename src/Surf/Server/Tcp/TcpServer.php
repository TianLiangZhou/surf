<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/1
 * Time: 21:18
 */

namespace Surf\Server\Tcp;


use Surf\Server\Server;
use Swoole\Server as SwooleServer;

class TcpServer extends Server
{

    /**
     * @return mixed|void
     */
    protected function bootstrap()
    {
        // TODO: Implement bootstrap() method.
        $this->server = new SwooleServer(
            $this->defaultConfig['host'], $this->defaultConfig['port']
        );
    }

    /**
     *
     */
    protected function listen()
    {
        // TODO: Implement listen() method.

        $this->server->on('connect', function(SwooleServer $server, int $fd, int $reactorId) {

        });

        $this->server->on('receive', function(SwooleServer $server, int $fd, int $reactorId, string $data) {

        });

        $this->server->on('close', function(SwooleServer $server, int $fd, int $reactorId) {

        });
    }
}