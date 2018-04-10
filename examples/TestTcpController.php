<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/10
 * Time: 9:59
 */

namespace Surf\Examples;

class TestTcpController extends \Surf\Mvc\Controller\TcpController
{
    public function name($body)
    {
        return "my name is " . $body['name'] .  ", my age is " . $body['age'] . ", My job is an " . $body['job'] ;
    }
}
