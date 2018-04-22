<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/14
 * Time: 21:19
 */

namespace Surf\Cache;


abstract class Driver implements DriverInterface
{

    /**
     * @var null|\Redis|\Memcached|\MongoClient
     */
    protected $driver = null;
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * @var int
     */
    protected $expire = 86400;

    /**
     * Driver constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;

        if (isset($this->options['prefix'])) {
            $this->prefix = $options['prefix'];
        }

        if (isset($this->options['expire'])) {
            $this->expire = (int) $options['expire'];
        }
    }

    /**
     * @return mixed|null
     */
    protected function getDriver()
    {
        if (is_callable($this->driver)) {
            $this->driver = call_user_func($this->driver, $this->options);
        }
        return $this->driver;
    }

    /**
     * @return mixed
     */
    abstract protected function getConnectionResolver();

    /**
     * @return mixed
     */
    abstract protected function getConnection();
    /**
     * @param $name
     * @param $arguments
     * @throws \ErrorException
     */
    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.

        $driver = $this->getConnection();

        if (method_exists($driver, $name)) {
            return call_user_func_array([$driver, $name], $arguments);
        }
        throw new \ErrorException("Call undefined method '$name'");
    }
}