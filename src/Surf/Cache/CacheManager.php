<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/3/8
 * Time: 18:01
 */

namespace Surf\Cache;

use Pimple\Psr11\Container;

class CacheManager
{
    /**
     * @var null|Container
     */
    protected $container = null;

    /**
     * @var array
     */
    private $connections = [];

    /**
     * @var null|DriverFactory
     */
    protected $driverFactory = null;

    /**
     * CacheManager constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->driverFactory = new DriverFactory();
    }

    /**
     * @param string $name
     * @return DriverInterface
     */
    public function connection($name = 'default')
    {
        if (!isset($this->connections[$name])) {
            $this->connections[$name] = $this->factory($name);
        }
        return $this->connections[$name];
    }

    /**
     * @param string $name
     * @return DriverInterface
     */
    public function factory($name = 'default')
    {
        return $this->makeConnection($name);
    }

    /**
     * @param $name
     * @return DriverInterface
     */
    protected function makeConnection($name)
    {
        $configure = $this->configuration($name);

        return $this->driverFactory->make($configure, $name);
    }

    /**
     * @param $name
     * @return array
     */
    protected function configuration($name)
    {
        $config = $this->container->get('server.config');

        $name = $name ?? 'default';

        return $config['cache'][$name] ?? [];
    }

}
