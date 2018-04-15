<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/3/2
 * Time: 15:23
 */

namespace Surf\Pool;

use Pimple\Psr11\Container;
use Surf\Cache\CacheManager;
use Surf\Database\DatabaseManager;
use Surf\Pool\Connections\CacheConnectionPool;
use Surf\Pool\Connections\ConnectionPool;
use Surf\Pool\Connections\DatabaseConnectionPool;
use Surf\Pool\Exception\MaxWaitException;
use Surf\Pool\Exception\NotFoundPoolException;

class PoolManager
{

    /**
     * @var array
     */
    protected $pool = [];

    /**
     * @var int
     */
    protected $interval = 100; //mill

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var null|Container
     */
    protected $container = null;

    /**
     * PoolManager constructor.
     * @param array $config
     * @param int $interval
     * @throws Exception\MaxOutException
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->initialize();

        $this->booted();
    }

    /**
     *
     */
    protected function initialize()
    {
        $globalConfig = $this->container->get('server.config');

        $this->setConfig($globalConfig['pool'] ?? []);

        $this->setInterval($globalConfig['pool']['interval'] ?? 100);
    }

    /**
     * @throws Exception\MaxOutException
     */
    protected function booted()
    {
        $config = $this->getConfig();

        $globalConfig = $this->container->get('server.config');

        foreach ($config as $namespace => $value) {
            if (!is_array($value)) {
                continue;
            }
            foreach ($value as $name => $configure) {
                if (is_numeric($name)) {
                    continue;
                }
                if (!isset($globalConfig[$namespace][$name]) && !isset($configure['callback'])) {
                    continue;
                }
                $connections = [];
                $connections[] = $this->factory($namespace . '.' . $name);
                $startNumber= $configure['start_number'] ?? 1;
                $maxNumber  = $configure['max_number'] ?? 20;
                $maxWaitTime= $configure['max_wait_time'] ?? 1;
                for ($i = 1; $i < $startNumber; $i++) {
                    $connections[] = $this->factory($namespace . '.' . $name);
                }
                $this->createPool($namespace . '.' . $name, $connections, $maxNumber, $maxWaitTime);
            }
        }
    }

    /**
     * @param $poolName
     * @return Connection
     */
    protected function factory($poolName)
    {
        list($namespace, $name) = explode('.', $poolName);
        $connection = null;
        if (in_array($namespace, ['database', 'cache'])) {
            /**
             * @var $manager CacheManager|DatabaseManager
             */
            $manager = $this->container->has($namespace . '.manager')
                ? $this->container->get($namespace . '.manager')
                : null;
            if ($manager) {
                $connection = $manager->factory($name);
            }
        } else {
            $configure = $this->config[$namespace][$name];
            $connection = is_callable($configure['callback'])
                ? call_user_func($configure['callback'], $this->container)
                : new $configure['callback']($this->container);
        }
        $class = null;
        switch ($namespace) {
            case 'database':
                $class = new DatabaseConnectionPool($this, $connection);
                break;
            case 'cache':
                $class = new CacheConnectionPool($this, $connection);
                break;
            default:
                $class = new ConnectionPool($this, $connection);
        }
        $class->setName($poolName);
        return $class;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function setInterval(int $interval)
    {
        $this->interval = $interval;
    }
    /**
     * @return int
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param string $name
     * @param array $connection
     * @return $this
     * @throws Exception\MaxOutException
     */
    protected function createPool(string $name, array $connections = [], int $max = 20, int $maxWaitTime = 1)
    {
        if (isset($this->pool[$name])) {
            return $this;
        }
        $this->pool[$name] = new Pool(null, $max, $maxWaitTime);
        foreach ($connections as $connection) {
            if ($connection instanceof Connection) {
                $this->push($name, $connection);
            }
        }
        return $this;
    }

    /**
     * @param string $name
     * @param $connection
     * @throws Exception\MaxOutException
     */
    public function push(string $name, Connection $connection)
    {
        if (!isset($this->pool[$name])) {
            $this->createPool($name);
        }
        $this->pool[$name]->push($connection);
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws MaxWaitException
     * @throws NotFoundPoolException
     * @throws Exception\MaxOutException
     */
    public function pop(string $name)
    {
        if (!isset($this->pool[$name])) {
            throw new NotFoundPoolException("Pool not found name to '$name'");
        }
        /**
         * @var $pool Pool
         */
        $pool = $this->pool[$name];
        $connection = $pool->pop();
        if ($connection === null) {
            if ($pool->currentQuantity() >= $pool->getMax()) {
                $startTime = time();
                while (($len = $pool->count()) < 1) {
                    if (time() - $startTime >= $pool->getMaxWaitTime()) {
                        throw new MaxWaitException('Exceeded waiting time ' . $pool->getMaxWaitTime() . ' second');
                        break;
                    }
                }
                $connection = $pool->pop();
            } else {
                $connection = $this->factory($name);
                $this->push($name, $connection);
                return $this->pop($name);
            }
        }
        return $connection;
    }

    /**
     * @param Connection $connection
     * @return bool
     */
    public function recycling(Connection $connection)
    {
        //$hash = $connection->getHash();
        $name = $connection->getName();
        $state = false;
        if (isset($this->pool[$name])) {
            $state = $this->pool[$name]->recycling($connection);
        }
        return $state;
    }

    /**
     *
     * @throws MaxWaitException
     */
    public function tickCallback()
    {
        $count = count($this->pool);
        if ($count < 1) {
            return ;
        }
        foreach ($this->pool as $namespace => $pool) {
            /**
             * @var $pool Pool
             */
            $size = $pool->currentQuantity();
            for ($i = 0; $i < $size; $i++) {
                $connection = $pool->pop();
                if ($connection === null) {
                    continue;
                }
                $connection->ping();
                $connection->close(); //回收对象
            }
        }
    }

    /**
     *
     */
    public function tick()
    {
        \swoole_timer_tick($this->interval, [$this, 'tickCallback']);
    }
}
