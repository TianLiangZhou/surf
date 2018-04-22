<?php
/**
 * 
 * 
 * 
 */

namespace Surf\Task;

use Pimple\Psr11\Container;
use Swoole\Server;

abstract class TaskHandle implements TaskHandleInterface
{

    /**
     * @var null
     */
    protected $content = null;

    /**
     * @var null|Server
     */
    protected $server  = null;

    /**
     * @var null | Container
     */
    protected $container = null;

    /**
     * @var null | int
     */
    protected $taskId = null;


    /**
     * @var null | int
     */
    protected $workerId = null;
    /**
     * 
     */
    public function __construct($content, int $taskId, int $workerId)
    {
        $this->content = $content;

        $this->taskId = $taskId;

        $this->workerId = $workerId;
    }

    /**
     * @param \Swoole\Server $server
     */
    public function setServer(Server $server)
    {
        $this->server = $server;
    }

    /**
     * @param null $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

}