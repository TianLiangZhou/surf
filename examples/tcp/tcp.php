<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/9
 * Time: 13:45
 */

include __DIR__ . '/../../vendor/autoload.php';

$config = include __DIR__ . '/../config.php';

$config['server'] = 'tcp';
$config['protocol'] = \Surf\Server\Tcp\Protocol\JsonProtocol::class;

$app = new \Surf\Application(__DIR__, [
    'app.config' => $config
]);
$app->addProtocol('user.name', Surf\Examples\TestTcpController::class . ':name');
$app->run();
