<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/1
 * Time: 21:17
 */

namespace Surf\Server\Http;

use Surf\Server\Server;
use Swoole\Http\Server as SwooleHttpServer;
use Swoole\Server as SwooleServer;

class HttpServer extends Server
{
    /**
     * @return mixed|void
     */
    protected function init()
    {
        // TODO: Implement bootstrap() method.
        $this->server = new SwooleHttpServer(
            $this->defaultConfig['host'],
            $this->defaultConfig['port']
        );
    }

    /**
     *
     */
    protected function listen()
    {
        // TODO: Implement listen() method.
        $kernel = $this->container->get('http.kernel');
        $this->server->on('request', [$kernel, 'handle']);
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

    /**
     *
     */
    protected function connect(\Swoole\Server $server, int $fd, int $reactorId)
    {
        // TODO: Implement connect() method.
    }

    /**
     *
     */
    protected function close(\Swoole\Server $server, int $fd, int $reactorId)
    {
        // TODO: Implement close() method.
    }
}
