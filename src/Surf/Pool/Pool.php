<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/3/1
 * Time: 15:12
 */

namespace Surf\Pool;

use SplFixedArray;
use SplQueue;
use Surf\Pool\Exception\MaxOutException;
use Surf\Pool\Exception\MaxWaitException;

/**
 * Class Pool
 * @package Surf\Pool
 */
class Pool
{

    /**
     * @var null|SplFixedArray
     */
    private $pool = null;

    /**
     * @var int
     */
    private $current = 0;

    /**
     * @var int
     */
    private $max = 50;

    /**
     * @var int
     */
    private $mix = 1;

    /**
     * @var int
     */
    private $maxWaitTime = 1; // (u: 1s)

    /**
     * Pool constructor.
     * @param array $connection
     * @throws MaxOutException
     */
    public function __construct(Connection $connection = null, int $max = 50, int $maxWaitTime = 1)
    {
        $this->pool = new SplQueue();
        $this->setMax($max);
        $this->setMaxWaitTime($maxWaitTime);
        if ($connection != null) {
            $this->push($connection);
        }
    }

    /**
     * @param $connection
     * @throws MaxOutException
     */
    public function push(Connection $connection)
    {
        if ($this->current >= $this->max) {
            throw new MaxOutException("Exceeded the maximum limit " . $this->getMax());
        }
        $this->pool->push($connection);
        $this->current++;
        return $this;
    }

    /**
     * @param Connection $connection
     * @return bool
     */
    public function recycling(Connection $connection)
    {
        return $this->pool->unshift($connection);
    }

    /**
     * @return mixed|Connection
     * @throws MaxWaitException
     */
    public function pop()
    {
        $currentConnection = null;
        if ($this->pool->count() > 0) {
            $currentConnection = $this->pool->pop();
        }
        return $currentConnection;
    }

    /**
     * @return Connection|null
     */
    public function top()
    {
        $currentConnection = null;
        if ($this->pool->count() > 0) {
            $currentConnection = $this->pool->top();
        }
        return $currentConnection;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->pool->count();
    }

    /**
     * @return int
     */
    public function currentQuantity()
    {
        return $this->current;
    }

    /**
     * @return int
     */
    public function getMix(): int
    {
        return $this->mix;
    }

    /**
     * @param int $mix
     */
    public function setMix(int $mix): void
    {
        $this->mix = $mix;
    }

    /**
     * @return int
     */
    public function getMax(): int
    {
        return $this->max;
    }

    /**
     * @param int $max
     */
    public function setMax(int $max): void
    {
        $this->max = $max;
    }

    /**
     * @return int
     */
    public function getMaxWaitTime(): int
    {
        return $this->maxWaitTime;
    }

    /**
     * @param int $maxWaitTime
     */
    public function setMaxWaitTime(int $maxWaitTime): void
    {
        $this->maxWaitTime = $maxWaitTime;
    }
}