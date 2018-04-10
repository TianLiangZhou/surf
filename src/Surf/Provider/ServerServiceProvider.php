<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/2
 * Time: 10:50
 */

namespace Surf\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Surf\Application;
use Surf\Server\Http\HttpServer;
use Surf\Server\Tcp\TcpServer;
use Surf\Server\WebSocket\WebSocketServer;

class ServerServiceProvider implements ServiceProviderInterface
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

        $pimple['server.config'] = function($pimple) {
            $config = $pimple['app.config'] ?? null;
            if ($config && is_string($config) && is_file($config)) {
                $config = require $config;
            }
            return $config ?? [];
        };
        $pimple['server'] = function($pimple) {
            /**
             * @var $pimple Application
             */
            $config = $pimple['server.config'];
            $serverName = $config['server'] ?? 'http';
            $server = null;
            $container = $pimple->getContainer();
            switch ($serverName) {
                case 'tcp':
                    $server = new TcpServer($container, $config);
                    break;
                case 'webSocket':
                    $server = new WebSocketServer($container, $config);
                    break;
                default:
                    $server = new HttpServer($container, $config);
            }
            return $server;
        };
    }
}