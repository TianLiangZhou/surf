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

abstract class Server
{
    /**
     * @var null
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
        $this->bootstrap();
        $this->initListen();
    }

    /**
     * @return mixed
     */
    protected abstract function bootstrap();

    /**
     *
     */
    protected function initListen()
    {
        $this->server->on('start', function(\Swoole\Server $server) {

        });
        $this->server->on('managerStart', function(\Swoole\Server $server) {

        });
        $this->server->on('workerStart', function(\Swoole\Server $server, int $workerId) {
            if ($workerId === 0) {
                /**
                 * @var $pool PoolManager|null
                 */
                $pool = $this->container->get('pool');
                //$pool && $pool->tick();
            }
        });

        $this->server->on('task', function(\Swoole\Server $server, int $taskId, int $workerId, $data) {

        });

        $this->server->on('finish', function(\Swoole\Server $server, int $taskId, $data) {

        });
        $this->listen();
    }

    protected abstract function listen();

    /**
     *
     */
    public function start()
    {
        $this->server->start();
    }
}