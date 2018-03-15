<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/5
 * Time: 10:00
 */

namespace Surf\Provider;


use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use FastRoute\DataGenerator\GroupCountBased;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Surf\Api\EventListenerProviderInterface;
use Surf\Application;
use Surf\Listeners\RouterListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RouterServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
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

        $pimple['router'] = function($pimple) {
            return new RouteCollector(new Std(), new GroupCountBased());
        };

    }

    /**
     * @param Container $app
     * @param EventDispatcherInterface $dispatcher
     */
    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        // TODO: Implement subscribe() method.
        $dispatcher->addSubscriber(new RouterListener(
            $app['router'], null, $app['server.config']['document_root'] ?? Application::$basePath
        ));
    }
}