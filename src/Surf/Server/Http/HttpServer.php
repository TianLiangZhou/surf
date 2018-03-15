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

class HttpServer extends Server
{
    /**
     * @return mixed|void
     */
    protected function bootstrap()
    {
        // TODO: Implement bootstrap() method.
        $this->server = new SwooleHttpServer(
            $this->defaultConfig['host'], $this->defaultConfig['port']
        );
        $this->server->set($this->defaultConfig['setting']);
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
}