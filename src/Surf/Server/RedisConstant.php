<?php
/**
 * Created by PhpStorm.
 * User: meShell
 * Date: 2018/4/22
 * Time: 10:53
 */

namespace Surf\Server;


final class RedisConstant
{
    const FULL_CONNECT_FD = 'full:connection:fd'; //sets type

    const TASK_FINISH_WORKER = 'task:finish:worker'; //sets type

    const FULL_FD_WORKER = 'full:fd:worker'; //Hashes type
}