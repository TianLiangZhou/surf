<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/11
 * Time: 21:07
 */

namespace Surf\Provider;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Surf\Session\Driver\File;
use Surf\Session\Driver\Redis;
use Surf\Session\DriverInterface;
use Surf\Session\SessionManager;

class SessionServiceProvider implements ServiceProviderInterface
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
        $pimple['session'] = $pimple->protect(function ($pimple, $sessionId = null) {

            /**
             * @var $pimple \Pimple\Psr11\Container
             */
            $options = $pimple->has('app.config')
                ? ($pimple->get('app.config')['session'] ?? [])
                : [];

            $class = isset($options['driver']) ? $options['driver'] : 'file';
            $driver = null;
            if (is_callable($driver)) {
                $driver = call_user_func($class, $pimple);
            }
            if (is_object($class)) {
                $driver = $class;
            }
            if (is_string($class)) {
                switch ($class) {
                    case 'redis':
                        $driver = new Redis();
                        break;
                    default:
                        $driver = new File();
                }
            }
            if (! ($driver instanceof DriverInterface)) {
                throw new \RuntimeException("Driver must implement to 'DriverInterface' interface");
            }
            return new SessionManager($driver, $sessionId, $options);
        });
    }
}