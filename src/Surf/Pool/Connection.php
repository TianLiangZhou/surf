<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/3/6
 * Time: 11:33
 */

namespace Surf\Pool;

abstract class Connection
{
    /**
     * @var null|string
     */
    protected $hashId = null;

    /**
     * @var null|PoolManager
     */
    protected $manager = null;

    /**
     * @var null
     */
    protected $connection = null;

    /**
     * @var null
     */
    protected $name = null;

    /**
     * Connection constructor.
     * @param PoolManager $manager
     */
    public function __construct(PoolManager $manager, $connection)
    {
        $this->manager = $manager;
        $this->hashId = $this->createHashId();
        $this->connection = $connection;
    }

    /**
     * @param int $length
     * @return string
     */
    private function createHashId(int $length = 16): string
    {
        // uniqid gives 16 chars, but you could adjust it to your needs.
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($length / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        }
        return substr(bin2hex($bytes), 0, $length);
    }

    /**
     * 回收对象
     * @return bool
     */
    public function close()
    {
        return $this->manager->recycling($this);
    }

    /**
     * @return null|string
     */
    public function getHash(): string
    {
        return $this->hashId;
    }

    abstract public function ping();
    /**
     * @param $name
     * @param $arguments
     * @return mixed|null
     */
    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        if ($this->connection) {
            return call_user_func_array([$this->connection, $name], $arguments);
        }
        return null;
    }

    /**
     *
     */
    public function __clone()
    {
        // TODO: Implement __clone() method.
        $this->hashId = $this->createHashId();
    }

    /**
     * @return null
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param null $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     *
     */
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        //$this->close();
    }
}
