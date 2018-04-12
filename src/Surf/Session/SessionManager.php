<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/11
 * Time: 21:04
 */

namespace Surf\Session;


class SessionManager
{
    /**
     * @var int
     */
    private $sessionActiveStatus = PHP_SESSION_DISABLED;

    /**
     * @var null|SessionStorage
     */
    private $sessionStorage = null;

    /**
     * @var null|DriverInterface
     */
    private $driver = null;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var null
     */
    private $sessionId = null;

    /**
     * SessionManager constructor.
     * @param DriverInterface $driver
     * @param array $options
     */
    public function __construct(DriverInterface $driver, $sessionId = null, array $options = [])
    {
        $this->driver = $driver;

        $this->options = $options;

        $this->sessionId = $sessionId;

        $this->sessionStorage = new SessionStorage();
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }
    /**
     *
     */
    public function start()
    {
        if ($this->sessionActiveStatus === PHP_SESSION_ACTIVE) {
            return ;
        }

        if ($this->sessionId === null) {
            $this->sessionId = $this->driver->open();
        } else {
            $this->sessionStorage->iterator($this->driver->read($this->sessionId));
        }
        $this->sessionActiveStatus = PHP_SESSION_ACTIVE;
    }

    public function save()
    {
        /**
         * @var $session \ArrayIterator
         */
        $session = $this->sessionStorage->getIterator();
        $this->driver->save($this->sessionId, $session);
    }

    /**
     *
     */
    public function read()
    {
        if ($this->sessionId !== null) {
            $this->sessionStorage->iterator($this->driver->read($this->sessionId));
        }
    }

    /**
     * @param string $key
     * @param $data
     */
    public function set(string $key, $data)
    {
        $this->sessionStorage->set($key, $data);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->sessionStorage->get($key);
    }
}
