<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/14
 * Time: 21:18
 */

namespace Surf\Cache\Driver;

use Surf\Cache\Driver;

/**
 * Class Redis
 * @package Surf\Cache\Driver
 */
class Redis extends Driver
{

    /**
     * Redis constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        parent::__construct($options);

        $this->driver = $this->getConnectionResolver();
    }


    /**
     * @param $key
     * @return bool|string
     */
    public function get($key)
    {
        // TODO: Implement get() method.
        return $this->getConnection()->get($this->prefix . $key);
    }

    /**
     * @param $key
     * @param $data
     * @param int $ttl
     * @return bool
     */
    public function set($key, $data, $ttl = 86400)
    {
        // TODO: Implement set() method.
        return $this->getConnection()->setex($this->prefix . $key, $ttl, $data);
    }

    /**
     * @param $key
     * @return int
     */
    public function delete($key)
    {
        // TODO: Implement delete() method.
        return $this->getConnection()->del($this->prefix . $key);
    }

    /**
     * @param $key
     * @return bool
     */
    public function exists($key)
    {
        // TODO: Implement exists() method.
        return $this->getConnection()->exists($this->prefix . $key);
    }

    /**
     * @return \Closure
     */
    protected function getConnectionResolver()
    {
        return function($options) {
            $redis = new \Redis();
            $redis->connect($options['host'] ?? '127.0.0.1', $options['port'] ?? 6379, $options['timeout'] ?? 0);
            if (isset($options['auth'])) {
                $redis->auth($options['auth']);
            }
            return $redis;
        };
    }

    protected function getConnection()
    {
        // TODO: Implement isConnection() method.
        /**
         * @var $redis \Redis
         */
        $redis = $this->getDriver();

        if ($redis->isConnected()) {
            return $redis;
        }
        $this->driver = $this->getConnectionResolver();
        return $this->getDriver();
    }
}