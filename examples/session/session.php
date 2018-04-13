<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/13
 * Time: 9:59
 */


require __DIR__ . '/../../vendor/autoload.php';

$app = new \Surf\Application(__DIR__, [
    'app.config' => __DIR__ . '/../config.php'
]);

$app->register(new \Surf\Provider\SessionServiceProvider());
$app->addGet('/', function () {
    return "Hello world";
});
$app->addGet('/test', \Surf\Examples\TestController::class . ':index');
try {
    $app->run();
} catch (\Surf\Exception\ServerNotFoundException $e) {

}