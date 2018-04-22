<?php
/**
 * Created by PhpStorm.
 * User: meShell
 * Date: 2018/4/21
 * Time: 22:09
 */

require __DIR__ . '/../../vendor/autoload.php';


$config = require  __DIR__ . '/../config.php';

$config['setting']['task_worker_num'] = 10;

$app = new \Surf\Application(__DIR__, [
    'app.config' => $config
]);

$app->addGet('/task', \Surf\Examples\TestController::class . ':taskTest');

try {
    $app->run();
} catch (\Surf\Exception\ServerNotFoundException $e) {

}
