# Surf

[![Build Status](https://travis-ci.org/TianLiangZhou/surf.svg?branch=master)](https://travis-ci.org/TianLiangZhou/surf)
[![License](https://img.shields.io/badge/license-mit-blue.svg)](LICENSE)
[![Coverage Status](https://coveralls.io/repos/github/TianLiangZhou/surf/badge.svg?branch=%28HEAD+detached+at+ab55c4e%29)](https://coveralls.io/github/TianLiangZhou/surf?branch=%28HEAD+detached+at+ab55c4e%29)
[![Maintainability](https://api.codeclimate.com/v1/badges/5c492204dfcf4157fc8b/maintainability)](https://codeclimate.com/github/TianLiangZhou/surf/maintainability)

`surf` 是一个对[`Swoole`](https://github.com/swoole/swoole-src)扩展库的封装，它可以像传统`MVC`一样编写 __Http__,__Tcp__ 应用.


Installation
------------

使用[Composer](https://getcomposer.org/)来安装框架.

```shell
$ composer require meshell/surf "^1.0"
```

Usage
-----

一个简单的`http`应用，创建一个配置文件`config.php`.

```php
# config.php

return [
    'host' => '0.0.0.0',
    'port' => 9527,
    'setting' => [
        'reactor_num' => 8,
        'worker_num' => 10,
    ]
];

```

创建一个`http.php`文件.

```php

require __DIR__ . '/vendor/autoload.php';

$app = new \Surf\Application(__DIR__, [
    'app.config' => __DIR__ . '/config.php'
]);

$app->addGet('/', function() {
    return "Hello world";    
});
$app->register(new \Surf\Provider\PoolServiceProvider());
$app->run();

```

启动服务.

```shell

$ php http.php

```

浏览器打开<http://127.0.0.1:9527/> 显示 "Hello world", 你也可以使用`curl`.

```shell
$ curl http://127.0.0.1:9527/
```

查看事例[http](examples/http/http.php)



一个简单的`tcp`应用, 配置文件还是使用上面的`config.php`, 创建一个`tcp.php`. `tcp`需要设置`protocol`解析类. 一般`tcp`服务端和客户端使需要约定消息格式才能解析出正确的数据. 在`surf`中我们使用的默认解析格式是包头，包长，包体(`unpack(A64header/Nlen/A*data)`)作为消息格式。调用每条协议我们是定义在`包头`中.

```php


include __DIR__ . '/vendor/autoload.php';

$config = include __DIR__ . '/config.php';

$config['server'] = 'tcp';
$config['protocol'] = \Surf\Server\Tcp\Protocol\JsonProtocol::class;

$app = new \Surf\Application(__DIR__, [
    'app.config' => $config
]);
$app->addProtocol('user.name', Examples\TestTcpController::class . ':name');
$app->run();

```

编写客户端程序`client.php`.

```php

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

```

启动服务

```php
$ php tcp.php
```

连接服务

```php
$ php client.php
```

打印服务返回内容: "my name is meShell, my age is 18, My job is an engineer";

查看事例[tcp](examples/tcp/tcp.php)

#### Connection pool

在`surf`中使用连接池功能.

```php
#config.php
return [
    ...
    'database' => [
        'default' => [
            'driver' => 'mysql',
            'host'   => 'localhost',
            'database' => 'test',
            'username' => 'root',
            'password' => "123456",
            'options'  => [],
        ],
    ],
    'cache' => [
        'default' => [
            'driver' => 'redis',
            'host'   => '127.0.0.1',
            'port'   => 6379,
            // 'prefix' => 'SURF:'
            // 'auth' => 'password'
        ],
    ],
    'pool' => [
        'interval' => 100, //心跳检测时间，以毫秒为单位
        'database' => [
            'default' => [
                'start_number' => 10 //默认开启
            ],
        ],
        'cache' => [
            'default' => [
                'start_number' => 10 //默认开启
            ],
        ],
    ],
];

```

在启动文件里注册`provider`.

```php
$app->register(new \Surf\Provider\PoolServiceProvider());
```

在`Controller`中获取连接池中的对象.

```php
/**
* @var $pool PoolManager
*/
$pool = $this->container->get('pool'); //获取连接池管理对象
/**
* @var $pdo \Surf\Pool\Connection 获取一个数据库对象
*/
$pdo  = $pool->pop('database.default'); //从database.default池子中获取当前对象

/**
* 获取一个缓存对象 需要注册 
* $app->register(new \Surf\Provider\CacheServiceProvider());
*/
$redis = $pool->pop('cache.default'); //从cache.default池子中获取当前对象
```

查看事例[pool](examples/TestController.php)


#### Usage `Session` and `Cookie`

`session` 和 `cookie` 在`web`开发中是经常需要使用的保存一些登录信息, 登录状态等等.

在`匿名函数`中使用. 在回调函数时候框架会自动将`路由变量`, `request`, `cookies` 这几个参数填充到函数.

```php

use Surf\Server\Http\Cookie\CookieAttributes;

$app->addGet('/', function($routeVars, Request $request, Cookies $cookies) {

    $session = $request->session; //获取session 对象
    $session->set('userInfo', ['id' => 1]);
    //使用cookie, 传入一个CookieAttributes对象
    $cookies->set(CookieAttributes::create('name', 'value', 0));
});

```

在`HttpController`中使用.

```php

use Surf\Server\Http\Cookie\CookieAttributes;

$app->addGet('/', 'SessionController:index');


...

class SessionController extends HttpController
{
    ...

    public function index($routeVars)
    {
        $session = $this->request->session; // or $this->session; 获取session 对象
        $session->set('userInfo', ['id' => 1]);
        //使用cookie       
        $this->cookies->set(CookieAttributes::create('name', 'value', 0));
    }
}

```

查看事例[session](examples/session/session.php)

#### 任务

在控制器中使用`$this->task()`, 这个是异步, 想使用同步可以`$this->syncTask()`.

```php

    ...
    public function taskTest()
    {
        $taskId = $this->task('push all message worker' . $this->workerId, PushTaskHandle::class);
        //$status = $this->syncTask('sync push all message', PushTaskHandle::class);
        //var_dump($status);
        return "task push id:" . $taskId . ", workId:" . $this->workerId;
    }

```

查看事例[task](examples/task/task.php)

#### 全局定时器

在有些业务中我们可能会有这样的需求，比如每隔两小时需要读取下订单数.但你也可以用`crontab`实现. 
相同时间的定时器会被最后一次添加的定时器覆盖,定时器时间单位为毫秒.


```php

...

$app->addTicker(100, \Surf\Examples\HeartbeatTicker::class);

try {
    $app->run();
} catch (\Surf\Exception\ServerNotFoundException $e) {

}

```

查看事例[ticker](examples/ticker/ticker.php)

## License

[LICENSE](LICENSE)