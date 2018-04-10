<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/3/8
 * Time: 14:10
 */

namespace Surf\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Surf\Application;
use Surf\Database\ConnectionFactory;
use Surf\Database\DatabaseManager;

class DatabaseServiceProvider implements ServiceProviderInterface
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

        $pimple['database.manager'] = function ($pimple) {
            /**
             * @var $pimple Application
             */
            return new DatabaseManager($pimple->getContainer(), new ConnectionFactory());
        };
    }
}
