<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/1
 * Time: 21:16
 */

namespace Surf\Server;

use Pimple\Psr11\Container;
use Surf\Cache\Driver\Redis;
use Surf\Pool\PoolManager;
use Surf\Task\TaskHandle;
use Swoole\Server as SwooleServer;
use Surf\Task\TaskHandleInterface;

abstract class Server
{
    /**
     * @var null|SwooleServer
     */
    protected $server = null;

    /**
     * @var array
     */
    protected $defaultConfig = [
        'host' => '0.0.0.0',
        'port' => 9527,
        'setting' => [

        ],
    ];

    /**
     * @var null|Container
     */
    protected $container = null;

    /**
     * @var array
     */
    protected $task = [

    ];
    /**
     * Server constructor.
     * @param Container $container
     * @param array $config
     */
    public function __construct(Container $container, array $config = [])
    {
        $this->container = $container;
        $this->defaultConfig = array_merge($this->defaultConfig, $config);
        $this->init();
        $this->bootstrap();
    }

    /**
     *
     */
    protected function bootstrap()
    {
        $this->server->set($this->defaultConfig['setting']);

        $this->server->on('start', [$this, 'onStart']);
        $this->server->on('managerStart', [$this, 'onManagerStart']);

        $this->server->on('workerStart', [$this, 'onWorkerStart']);
        $this->server->on('connect', [$this, 'onConnect']);
        $this->server->on('task', [$this, 'onTask']);
        $this->server->on('finish', [$this, 'onFinish']);
        $this->server->on('close', [$this, 'onClose']);
        $this->listen();
    }

    /**
     * @return mixed
     */
    abstract protected function init();

    /**
     * @return mixed
     */
    abstract protected function listen();

    /**
     * @param SwooleServer $server
     * @return mixed
     */
    abstract protected function start(\Swoole\Server $server);

    /**
     * @param SwooleServer $server
     * @param int $workerId
     * @return mixed
     */
    abstract protected function workerStart(\Swoole\Server $server, int $workerId);

    /**
     * @param SwooleServer $server
     * @return mixed
     */
    abstract protected function managerStart(\Swoole\Server $server);

    /**
     * @param SwooleServer $server
     * @param int $fd
     * @param int $reactorId
     * @return mixed
     */
    abstract protected function connect(\Swoole\Server $server, int $fd, int $reactorId);

    /**
     * @param SwooleServer $server
     * @param int $fd
     * @param int $reactorId
     * @return mixed
     */
    abstract protected function close(\Swoole\Server $server, int $fd, int $reactorId);

    /**
     *
     */
    public function run()
    {
        $this->server->start();
    }

    /**
     * 启动主进程，设置主进程名称
     * @param SwooleServer $server
     */
    public function onStart(\Swoole\Server $server)
    {
        $this->start($server);
        swoole_set_process_name('surf:master');
    }

    /**
     * 启动管理进程，设置管理进程名字
     * @param SwooleServer $server
     */
    public function onManagerStart(\Swoole\Server $server)
    {
        $this->managerStart($server);
        swoole_set_process_name('surf:manager');
    }

    /**
     * 启动worker进程，设置worker进程名称
     * @param SwooleServer $server
     * @param int $workerId
     */
    public function onWorkerStart(\Swoole\Server $server, int $workerId)
    {
        if ($workerId === 0) {
            /**
             * @var $pool PoolManager|null
             */
            if ($this->container->has('pool')) {
                $pool = $this->container->get('pool');
                $pool && $pool->tick();
            }
        }
        $this->workerStart($server, $workerId);
        $workerNumber = $this->defaultConfig['setting']['worker_num'] ?? 1;
        if ($workerId >= $workerNumber) {
            swoole_set_process_name('surf:task');
        } else {
            swoole_set_process_name('surf:worker');
        }
    }

    /**
     * @param SwooleServer $server 注意思在模式1、3情况下此回调不会被调用onClose也是一样的
     * @param int $fd
     * @param int $reactorId
     */
    public function onConnect(SwooleServer $server, int $fd, int $reactorId)
    {
        if ($this->container->has('redis')) {
            /**
             * @var $redis \Redis|Redis 保存所有连接fd到redis
             */
            $redis = $this->container->get('redis');
            $redis->sAdd(RedisConstant::FULL_CONNECT_FD, $fd);
            $redis->hSet(RedisConstant::FULL_FD_WORKER, $fd, $server->worker_id);
        }

        $this->connect($server, $fd, $reactorId);
    }

    /**
     * @param SwooleServer $server
     * @param int $fd
     * @param int $reactorId
     * @throws \ErrorException
     */
    public function onClose(SwooleServer $server, int $fd, int $reactorId)
    {
        if ($this->container->has('redis')) {
            /**
             * @var $redis Redis|\Redis
             */
            $redis = $this->container->get('redis');
            $redis->sRem(RedisConstant::FULL_CONNECT_FD, $fd);
            $redis->hDel(RedisConstant::FULL_FD_WORKER, $fd);
        }
        $this->close($server, $fd, $reactorId);
    }

    /**
     * @param SwooleServer $server
     * @param int $taskId
     * @param int $workerId
     * @param $data
     */
    public function onTask(SwooleServer $server, int $taskId, int $workerId, $data)
    {
        $serialize = unserialize($data);
        if (empty($serialize)) {
            return 'Unserialize failed';
        }
        list($content, $handleClass) = $serialize;
        
        if (!class_exists($handleClass)) {
            return 'Handle class not exists';
        }
        /**
         * @var $class TaskHandle
         */
        $class = new $handleClass($content, $taskId, $workerId);
        if (!($class instanceof TaskHandleInterface)) {
            return "Handle class must implements 'TaskHandleInterface'";
        }
        $class->setServer($server);
        $class->setContainer($this->container);
        try {
            $finish = $class->execute();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $finish;
    }

    /**
     * 上面的onTask任务执行完成之后调用该回调.
     * @param SwooleServer $server
     * @param int $taskId
     * @param $data
     */
    public function onFinish(SwooleServer $server, int $taskId, $data)
    {
        if (is_bool($data) && $data == true) {
            if ($this->container->has('redis')) {
                /**
                 * @var $redis Redis|\Redis
                 */
                $redis = $this->container->get('redis');
                $redis->sAdd(RedisConstant::TASK_FINISH_WORKER, "{$server->worker_id}:$taskId");
            }

        } else {
            $message = "workerId {$server->worker_id}, taskId $taskId, $data";
            if ($this->container->has('logger')) {
                /**
                 * @var $logger
                 */
                $logger = $this->container->get('logger');
                $logger->info($message);
            } else {
                $logFile = $this->defaultConfig['setting']['log_file'] ?? '/tmp/swoole.task.log';
                $format = sprintf(
                    '[%s] INFO: %s',
                    date('Y-m-d H:i:s'),
                    $message
                );
                file_put_contents($logFile, $format);
            }
        }
    }

    /**
     * @return null|SwooleServer
     */
    public function getServer()
    {
        return $this->server;
    }
}
