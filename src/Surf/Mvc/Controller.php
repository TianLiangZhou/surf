<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/4
 * Time: 11:20
 */

namespace Surf\Mvc;

use Pimple\Psr11\Container;
use Surf\Server\Server;

abstract class Controller
{

    /**
     * @var null | Container
     */
    protected $container = null;

    /**
     * @var null | Server;
     */
    private $server    = null;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var int 
     */
    protected $workerId = 0;

    /**
     * @var bool 是否主动关闭连接
     */
    protected $isClose = false;

    /**
     * @var null|\Swoole\Server
     */
    private $swooleServer = null;
    /**
     * Controller constructor.
     * @param Container $container
     * @param int $workerId
     */
    public function __construct(Container $container, int $workerId = 0)
    {
        $this->container = $container;

        $this->server = $this->container->get('server');

        $this->config = $container->get('server.config');

        $this->workerId = $workerId;

        $this->swooleServer = $this->server->getServer();

    }
    
    /**
     * 投递一个异步任务到task进程
     * @param mixed $content
     * @param string $handleClass
     * @return int|bool
     */
    public function task($content, string $handleClass)
    {
        if (!isset($this->config['setting']['task_worker_num']) || $this->config['setting']['task_worker_num'] < 1) {
            throw new \RuntimeException("Not configure 'task_worker_num'");
        } 
        return $this->swooleServer->task(serialize([$content, $handleClass]));
    }
    
    /**
     * * 投递一个同步任务到task进程，同步意味着会阻塞, 同步任务在执行完成会返回任务结果
     * @param mixed $content
     * @param string $handleClass
     * @return mixed
     */
    public function syncTask($content, string $handleClass, $timeout = 0.5, int $workerTaskId = -1)
    {
        if (!isset($this->config['setting']['task_worker_num']) || $this->config['setting']['task_worker_num'] < 1) {
            throw new \RuntimeException("Not configure 'task_worker_num'");
        } 
        return $this->swooleServer->taskwait(serialize([$content, $handleClass]), $timeout, $workerTaskId);
    }

    /**
     * 添加一个延迟定时器，在执行完成后会被销毁, 比如用户做完一个操作，我们需要1分钟之后给他发邮件或消息
     * @param int $mill
     * @param callable $callback
     */
    public function after(int $mill, callable $callback)
    {
        $this->swooleServer->after($mill, $callback);
    }
    
    /**
     * 延后执行一个回调函数, 比如关闭某个链接
     * @param callable $callback
     */
    public function defer(callable $callback)
    {
        $this->swooleServer->defer($callback);
    }

    /**
     * @return bool
     */
    public function isClose(): bool
    {
        return $this->isClose;
    }

    /**
     * @param bool $isClose
     */
    public function setIsClose(bool $isClose)
    {
        $this->isClose = $isClose;
    }
}
