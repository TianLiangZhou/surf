<?php
/**
 * Created by PhpStorm.
 * User: meShell
 * Date: 2018/4/22
 * Time: 11:46
 */

namespace Surf\Provider;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Surf\Cache\CacheManager;
use Surf\Cache\Driver\Redis;

class RedisServiceProvider implements ServiceProviderInterface
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

        /**
         * @param $pimple
         * @return Redis
         */
        $pimple['redis'] = function($pimple) {
            /**
             * @var $pimple Container
             */
            if ($pimple->offsetExists('cache.manager')) {
                /**
                 * @var $manager CacheManager
                 */
                $manager = $pimple->offsetGet('cache.manager');
                return $manager->connection('redis');
            }
            $config = $pimple->offsetGet('server.config');
            $redisConfig = $config['cache']['redis'] ?? ['host' => '127.0.0.1', 'port' => 6379];
            return new Redis($redisConfig);
        };
    }
}