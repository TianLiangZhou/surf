<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/1
 * Time: 21:19
 */

namespace Surf\Server\WebSocket;

use Surf\Server\Server;
use Swoole\Http\Request;
use Swoole\Server as SwooleServer;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as SwooleWebSocketServer;

class WebSocketServer extends Server
{

    protected function bootstrap()
    {
        // TODO: Implement bootstrap() method.
        $this->server = new SwooleWebSocketServer(
            $this->defaultConfig['host'], $this->defaultConfig['port']
        );
    }

    protected function listen()
    {
        // TODO: Implement listen() method.

        $this->server->on('open', function (SwooleWebSocketServer $server, Request $request) {

        });

        $this->server->on('message', function (SwooleServer $server, Frame $frame) {

        });
    }
}