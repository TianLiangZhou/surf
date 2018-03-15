<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/3/8
 * Time: 17:59
 */

namespace Surf\Provider;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Surf\Application;
use Surf\Cache\CacheManager;

class CacheServiceProvider implements ServiceProviderInterface
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

        $pimple['cache.manager'] = function($pimple) {

            /**
             * @var $pimple Application
             */
            return new CacheManager($pimple->getContainer());
        };
    }
}