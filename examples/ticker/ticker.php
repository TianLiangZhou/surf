<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang01
 * Date: 2018/4/26
 * Time: 15:10
 */

require __DIR__ . '/../../vendor/autoload.php';


$config = require  __DIR__ . '/../config.php';

$config['server'] = 'tcp';

$app = new \Surf\Application(__DIR__, [
    'app.config' => $config
]);

$app->register(new \Surf\Provider\RedisServiceProvider());

$app->addTicker(100, \Surf\Examples\HeartbeatTicker::class);

try {
    $app->run();
} catch (\Surf\Exception\ServerNotFoundException $e) {

}