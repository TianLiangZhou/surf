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
     * @var string
     */
    private $name = 'SURF_SESSION_ID';


    /**
     * @var string
     */
    private $mode = 'cookie';

    /**
     * @var int
     */
    private $expire = 7200;

    /**
     * SessionManager constructor.
     * @param DriverInterface $driver
     * @param array $options
     */
    public function __construct(DriverInterface $driver, $sessionId = null, array $options = [])
    {
        $this->options = $options;
        $this->setter();
        $this->sessionId = $sessionId;
        $this->sessionStorage = new SessionStorage();
        $this->driver = $driver;
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

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key)
    {
        return $this->sessionStorage->has($key);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode(string $mode): void
    {
        $this->mode = $mode;
    }

    /**
     * @return int
     */
    public function getExpire(): int
    {
        return $this->expire;
    }

    /**
     * @param int $expire
     */
    public function setExpire(int $expire): void
    {
        $this->expire = $expire;
    }

    /**
     * @param array $options
     */
    private function setter()
    {
        $default = ['name', 'mode', 'expire'];

        foreach ($default as $name) {
            if (isset($this->options[$name])) {
                $method = 'set' . ucfirst($name);
                $this->$method($this->options[$name]);
            }
        }
    }

    /**
     * @return null|DriverInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }
}
