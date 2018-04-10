<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/1
 * Time: 21:16
 */

namespace Surf\Server;

use Pimple\Psr11\Container;
use Surf\Pool\PoolManager;
use Swoole\Server as SwooleServer;

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
        $this->server->on('task', [$this, 'task']);
        $this->server->on('finish', [$this, 'finish']);
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
     *
     */
    public function run()
    {
        $this->server->start();
    }

    /**
     * @param SwooleServer $server
     */
    public function onStart(\Swoole\Server $server)
    {
        $this->start($server);
    }

    /**
     * @param SwooleServer $server
     */
    public function onManagerStart(\Swoole\Server $server)
    {
        $this->managerStart($server);
    }
    /**
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
    }

    /**
     * @param SwooleServer $server
     * @param int $taskId
     * @param int $workerId
     * @param $data
     */
    public function task(\Swoole\Server $server, int $taskId, int $workerId, $data)
    {
    }

    /**
     * @param SwooleServer $server
     * @param int $taskId
     * @param $data
     */
    public function finish(\Swoole\Server $server, int $taskId, $data)
    {
    }

    /**
     * @param SwooleServer $server
     * @param int $fd
     * @param int $reactorId
     */
    public function connect(SwooleServer $server, int $fd, int $reactorId)
    {
    }

    /**
     * @param SwooleServer $server
     * @param int $fd
     * @param int $reactorId
     */
    public function close(SwooleServer $server, int $fd, int $reactorId)
    {
    }
}
