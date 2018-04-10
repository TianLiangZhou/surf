<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/9
 * Time: 22:42
 */

namespace Surf\Server\Tcp;


use Surf\Collection\Collection;

class ProtocolCollector extends Collection
{
    /**
     * @var string
     */
    protected $currentGroupPrefix = '';

    /**
     * @param string $name
     * @param mixed $callback
     */
    public function add(string $name, $callback)
    {
        $this->offsetSet($name, $callback);
    }

    /**
     * @param string $prefix
     * @param callable $callback
     * @param string $join
     */
    public function addGroup(string $prefix, callable $callback)
    {
        $previousGroupPrefix = $this->currentGroupPrefix;
        $this->currentGroupPrefix = $previousGroupPrefix . $prefix;
        $callback($this);
        $this->currentGroupPrefix = $previousGroupPrefix;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->offsetGet($name);
    }
}