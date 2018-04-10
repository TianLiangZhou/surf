<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/1
 * Time: 19:52
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = new \Surf\Application(__DIR__, [
    'app.config' => __DIR__ . '/../config.php'
]);

$app->addGet('/', function () {
    return "Hello world";
});
$app->addGet('/test', \Surf\Examples\TestController::class . ':index');
$app->register(new \Surf\Provider\PoolServiceProvider());
$app->run();
