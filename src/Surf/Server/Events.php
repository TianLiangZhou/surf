<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/27
 * Time: 17:05
 */

namespace Surf\Server;

/**
 * Class Events
 * @package Surf\Server
 */
class Events
{
    const REQUEST = 'http.request';

    const CONTROLLER = 'http.controller';

    const RESPONSE = 'http.response';

    const FINISH_REQUEST = 'http.finish_request';
}
