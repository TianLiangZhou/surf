<?php
/**
 * Created by PhpStorm.
 * User: meShell
 * Date: 2018/4/30
 * Time: 16:23
 */

namespace Surf\Examples;


class WebsocketController extends \Surf\Mvc\Controller\WebSocketController
{

    public function userInfo($body)
    {
        var_dump($body);
        return [
            'username' => 'Hello world',
        ];
    }
}