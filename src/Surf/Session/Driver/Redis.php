<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/11
 * Time: 21:02
 */

namespace Surf\Session\Driver;

use Surf\Session\SessionDriver;

class Redis extends SessionDriver
{
    private $redis = null;

    /**
     * Redis constructor.
     * @param array $options
     */
    public function __construct(\Redis $redis, array $options = [])
    {
        $this->redis = $redis;
        parent::__construct($options);
    }

    /**
     * @return string
     */
    public function open()
    {
        // TODO: Implement open() method.
        $sessionId = $this->generateId();
        return $sessionId;
    }

    /**
     * @param string $id
     * @return \ArrayIterator
     */
    public function read(string $id)
    {
        // TODO: Implement read() method.

        $session = $this->redis->get('sess:' . $id);
        if ($session) {
            return unserialize($session);
        }
        return new \ArrayIterator();
    }

    /**
     * @param string $id
     * @param $data
     * @return mixed
     */
    public function save(string $id, $data)
    {
        // TODO: Implement save() method.
        return $this->redis->setex('sess:' . $id, $this->getExpire(), serialize($data));
    }
}
