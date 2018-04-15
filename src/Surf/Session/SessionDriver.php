<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/12
 * Time: 10:14
 */

namespace Surf\Session;

abstract class SessionDriver implements DriverInterface
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     *
     */
    const RANDOM_BYTE = 13;

    /**
     * @var int
     */
    protected $expire = 7200;

    public function __construct(array $options = [])
    {
        $this->options = $options;
        if (isset($this->options['expire'])) {
            $this->setExpire($options['expire']);
        }
    }

    /**
     * @param int $expire
     */
    public function setExpire(int $expire = 7200)
    {
        $this->expire = $expire;
    }

    /**
     * @return int
     */
    public function getExpire(): int
    {
        return $this->expire;
    }

    /**
     * @return string
     */
    protected function generateId()
    {
        return bin2hex(random_bytes(static::RANDOM_BYTE));
    }
}
