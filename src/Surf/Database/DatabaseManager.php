<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/3/7
 * Time: 14:05
 */

namespace Surf\Database;


use Pimple\Psr11\Container;

class DatabaseManager
{
    /**
     * @var null|ConnectionFactory
     */
    protected $factory = null;

    /**
     * @var null|Container
     */
    protected $container = null;
    /**
     * @var array
     */
    private $connections = [];

    public function __construct(Container $container, ConnectionFactory $factory)
    {
        $this->container = $container;

        $this->factory = $factory;
    }

    /**
     * @param null $name
     * @return Connection
     */
    public function connection($name = null)
    {
        $name = $name ?? 'default';
        if (!isset($this->connections[$name])) {
            $this->connections[$name] = $this->factory($name);
        }
        return $this->connections[$name];
    }

    /**
     * @param null $name
     * @return Connection
     */
    public function factory($name = null)
    {
        return $this->configure($this->makeConnection($name));
    }

    /**
     * @param $name
     * @return Connection
     */
    protected function makeConnection($name)
    {
        $config = $this->configuration($name);

        return $this->factory->make($config, $name);
    }

    /**
     * @param $name
     * @return array
     */
    protected function configuration($name)
    {
        $config = $this->container->get('server.config');

        $name = $name ?? 'default';

        return $config['database'][$name] ?? [];
    }

    /**
     * @param Connection $connection
     * @return Connection
     */
    protected function configure(Connection $connection)
    {
        $connection->setReconnectResolver(function(Connection $connection) {
            return $this->reconnect($connection->getName());
        });
        return $connection;
    }

    /**
     * @param string $name
     * @return Connection
     */
    protected function reconnect(string $name)
    {
        return $this->refreshPdoConnections($name);
    }

    /**
     * @param $name
     * @return Connection
     */
    protected function refreshPdoConnections($name)
    {
        $refresh = $this->makeConnection($name);
        return $refresh->getPdo();
    }
}