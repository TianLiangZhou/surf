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
    private $redis = null;

    /**
     * Redis constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        parent::__construct($options);

        $this->redis = self::getConnection();
    }

    /**
     * @return \Redis
     */
    protected function getRedis()
    {
        if (is_callable($this->redis)) {
            $this->redis = call_user_func($this->redis, $this->options);
        }
        return $this->redis;
    }

    /**
     * @param $key
     * @return bool|string
     */
    public function get($key)
    {
        // TODO: Implement get() method.
        return $this->getRedis()->get($this->prefix . $key);
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
        return $this->getRedis()->setex($this->prefix . $key, $ttl, $data);
    }

    /**
     * @param $key
     * @return int
     */
    public function delete($key)
    {
        // TODO: Implement delete() method.
        return $this->getRedis()->del($this->prefix . $key);
    }

    /**
     * @param $key
     * @return bool
     */
    public function exists($key)
    {
        // TODO: Implement exists() method.
        return $this->getRedis()->exists($this->prefix . $key);
    }

    /**
     * @return \Closure
     */
    private static function getConnection()
    {
        return function($options) {
            $redis = new \Redis();
            $redis->connect($options['host'], $options['port'] ?? 6379, $options['timeout'] ?? 0);
            if (isset($options['auth'])) {
                $redis->auth($options['auth']);
            }
            return $redis;
        };
    }
}