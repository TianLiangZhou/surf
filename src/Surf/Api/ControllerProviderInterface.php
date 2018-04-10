<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Surf\Api;

use Surf\Application;

/**
 * Interface for controller providers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface ControllerProviderInterface
{
    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return A ControllerCollection instance
     */
    public function connect(Application $app);
}
