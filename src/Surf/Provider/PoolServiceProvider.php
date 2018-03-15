<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/3/2
 * Time: 17:10
 */

namespace Surf\Provider;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Surf\Application;
use Surf\Database\ConnectionFactory;
use Surf\Pool\PoolManager;

class PoolServiceProvider implements ServiceProviderInterface
{

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        // TODO: Implement register() method.

        $pimple['pool'] = function($pimple) {
            /**
             * @var $pimple Application
             */
            return new PoolManager($pimple->getContainer());
        };
    }
}