<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/11
 * Time: 22:17
 */

namespace Surf\Session;


use Surf\Collection\Collection;

class SessionStorage extends Collection
{
    /**
     * @param string $key
     * @param $data
     */
    public function set(string $key, $data)
    {
        $this->offsetSet($key, $data);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        $this->offsetGet($key);
    }

    /**
     * @param array $items
     */
    public function iterator(\ArrayIterator $items)
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
    }
}
