<?php
/**
 * Created by PhpStorm.
 * User: meShell
 * Date: 2018/4/21
 * Time: 22:07
 */

for ($i = 0; $i < 10; $i++) {
    $client = new Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);


    $client->on('receive', function ($client, $data) {

        echo $data, "\n";

    });

    $client->on('close', function ($client) {

    });

    $client->on('error', function ($client) {
        echo "error code:" . $client->errCode;
    });

    $client->on('connect', function ($client) {
        $message = json_encode([
            'name' => 'meShell',
            'age' => 18,
            'job' => 'engineer'
        ]);
        $hex = pack('A64NA*', "user.task", strlen($message), $message);
        $client->send($hex);
    });
    if (!$client->connect('127.0.0.1', 9527, -1)) {
        exit("connection failed");
    }
}
