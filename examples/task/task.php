<?php
/**
 * Created by PhpStorm.
 * User: meShell
 * Date: 2018/4/21
 * Time: 22:03
 */

require __DIR__ . '/../../vendor/autoload.php';


$config = require  __DIR__ . '/../config.php';

$config['setting']['dispatch_mode'] = 2;
$config['setting']['task_worker_num'] = 10;


$config['server'] = 'tcp';

$config['protocol'] = \Surf\Server\Tcp\Protocol\JsonProtocol::class;

$app = new \Surf\Application(__DIR__, [
    'app.config' => $config
]);

$app->register(new \Surf\Provider\RedisServiceProvider());

$app->addProtocol('user.task', \Surf\Examples\TestTcpController::class . ':taskTest');

try {
    $app->run();
} catch (\Surf\Exception\ServerNotFoundException $e) {

}

