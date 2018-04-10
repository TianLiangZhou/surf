<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/3/8
 * Time: 18:01
 */

namespace Surf\Cache;

use Pimple\Psr11\Container;

class CacheManager
{
    protected $container = null;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function connection($name)
    {
    }

    public function factory($name)
    {
    }
}
