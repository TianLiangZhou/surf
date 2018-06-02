<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang01
 * Date: 2018/6/2
 * Time: 13:14
 */

namespace Surf\Event;


use Swoole\Server;
use Symfony\Component\EventDispatcher\Event;

class ServerEvent extends Event
{

    /**
     * @var null|Server
     */
    protected $server = null;

    /**
     * @var int
     */
    protected $fd = 0;
    /**
     * @var int
     */
    protected $workerId = 0;
    /**
     * ServerEvent constructor.
     * @param Server $server
     * @param int $fd
     * @param int $workerId
     */
    public function __construct(Server $server, int $fd = 0, int $workerId = 0)
    {
        $this->server = $server;

        $this->fd = $fd;

        $this->workerId = $workerId;
    }
}