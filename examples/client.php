<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang01
 * Date: 2018/4/24
 * Time: 16:45
 */

use Swoole\Client;

$body = [
    'cmdid' => 'Lpl.Gift',
    'data'  => [
        'propId'=> 10387,
        'uid' => 15,
    ],
];
// 所有参数数据包
$body = json_encode($body);
$toType = 11000;
$toIp = -1;
$fromType = 0;
$fromIp = 0;
$nowAskId = crc32(uniqid(microtime(), true));
$askId2 = 0;
$head = pack('IsIsIII', strlen($body), $toType, $toIp, $fromType, $fromIp, $nowAskId, $askId2);
$body = $head . $body;


$client = new Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 20002, 0.5)) {
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send($body);
echo $client->recv();
$client->close();