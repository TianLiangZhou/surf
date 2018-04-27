<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang01
 * Date: 2018/4/26
 * Time: 14:27
 */

namespace Surf\Ticker;


use Pimple\Psr11\Container;
use Swoole\Server;

abstract class Ticker implements TickerInterface
{

    /**
     * @var null |Container
     */
    protected $container = null;

    /**
     * @var null | Server
     */
    protected $server = null;

    /**
     * @var int
     */
    protected $interval = 0;

    /**
     * @param null|Server $server
     */
    public function setServer(Server $server)
    {
        $this->server = $server;
    }

    /**
     * @param null|Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param int $interval
     */
    public function setInterval(int $interval)
    {
        $this->interval = $interval;
    }
}