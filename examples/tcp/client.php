<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/9
 * Time: 13:54
 */

$client = new Swoole\Client(SWOOLE_SOCK_TCP);

if (!$client->connect('127.0.0.1', 9527, -1)) {
    exit("connection failed");
}

$message = json_encode([
    'name' => 'meShell',
    'age'  => 18,
    'job' => 'engineer'
]);
$hex = pack('A64NA*', "name=user.name;format=json", strlen($message), $message);

$client->send($hex);

echo $client->recv();

$client->close();
