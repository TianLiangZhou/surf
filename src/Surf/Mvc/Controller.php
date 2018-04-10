<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/4
 * Time: 11:20
 */

namespace Surf\Mvc;

use Pimple\Psr11\Container;

abstract class Controller
{

    /**
     * @var null | Container
     */
    protected $container = null;

    /**
     * @var null
     */
    private $server    = null;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }
}
