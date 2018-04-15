<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/14
 * Time: 21:16
 */

namespace Surf\Cache;


interface DriverInterface
{
    public function get($key);

    public function set($key, $data, $ttl = 86400);

    public function delete($key);

    public function exists($key);
}