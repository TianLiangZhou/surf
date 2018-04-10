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

    protected function init()
    {
        // TODO: Implement bootstrap() method.
        $this->server = new SwooleWebSocketServer(
            $this->defaultConfig['host'], $this->defaultConfig['port']
        );
    }

    protected function listen()
    {
        // TODO: Implement listen() method.

        $this->server->on('open', [$this, 'open']);

        $this->server->on('message', [$this, 'message']);
    }

    /**
     * @param SwooleWebSocketServer $server
     * @param Request $request
     */
    public function open(SwooleWebSocketServer $server, Request $request)
    {

    }

    /**
     * @param SwooleWebSocketServer $server
     * @param Frame $frame
     */
    public function message(SwooleWebSocketServer $server, Frame $frame)
    {

    }

    /**
     * @param SwooleServer $server
     * @return mixed
     */
    protected function start(\Swoole\Server $server)
    {
        // TODO: Implement start() method.
    }

    /**
     * @param SwooleServer $server
     * @param int $workerId
     * @return mixed
     */
    protected function workerStart(\Swoole\Server $server, int $workerId)
    {
        // TODO: Implement workerStart() method.
    }

    /**
     * @param SwooleServer $server
     * @return mixed
     */
    protected function managerStart(\Swoole\Server $server)
    {
        // TODO: Implement managerStart() method.
    }
}