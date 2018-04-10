<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/5
 * Time: 15:33
 */

namespace Surf\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Surf\Application;
use Surf\Server\Http\HttpKernel;
use Symfony\Component\EventDispatcher\EventDispatcher;

class KernelServiceProvider implements ServiceProviderInterface
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

        $pimple['dispatcher'] = function ($pimple) {
            return new EventDispatcher();
        };

        $pimple['http.kernel'] = function ($pimple) {
            /**
             * @var $pimple Application
             */
            return new HttpKernel($pimple['dispatcher'], $pimple->getContainer());
        };
        $pimple['tcp.kernel']  = function ($pimple) {
            return null;
        };

        $pimple['webSocket.kernel'] = function () {
            return null;
        };
    }
}
