<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/14
 * Time: 21:08
 */

namespace Surf\Cache;


use Surf\Cache\Driver\Redis;

class DriverFactory
{
    /**
     * @param $configure
     * @param $name
     * @return DriverInterface
     */
    public function make($configure, $name)
    {
        if (!isset($configure['driver'])) {
            throw new \InvalidArgumentException('invalid argument \'$configure\'');
        }
        $driver = null;
        switch ($configure['driver']) {
            case 'redis':
                $driver = new Redis($configure);
                break;
        }
        if ($driver == null) {
            throw new \InvalidArgumentException("Not found $driver driver");
        }
        return $driver;
    }
}