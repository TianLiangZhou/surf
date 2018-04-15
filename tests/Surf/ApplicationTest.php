<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/1
 * Time: 15:54
 */

namespace Surf\Test;

use FastRoute\RouteCollector;
use PHPUnit\Framework\TestCase;
use Pimple\Psr11\Container;
use Surf\Application;
use Surf\Cache\CacheManager;
use Surf\Event\GetResponseEvent;
use Surf\Examples\TestController;
use Surf\Examples\TestTcpController;
use Surf\Exception\MethodNotAllowedException;
use Surf\Exception\NotFoundHttpException;
use Surf\Pool\Connection;
use Surf\Pool\Connections\CacheConnectionPool;
use Surf\Pool\Connections\ConnectionPool;
use Surf\Pool\Exception\MaxOutException;
use Surf\Pool\Exception\MaxWaitException;
use Surf\Pool\Exception\NotFoundPoolException;
use Surf\Pool\PoolManager;
use Surf\Provider\CacheServiceProvider;
use Surf\Provider\PoolServiceProvider;
use Surf\Provider\SessionServiceProvider;
use Surf\Server\Events;
use Surf\Server\Http\HttpKernel;
use Surf\Server\Http\HttpServer;
use Surf\Server\Tcp\Protocol\JsonProtocol;
use Surf\Server\Tcp\ProtocolCollector;
use Surf\Server\Tcp\TcpServer;
use Surf\Server\WebSocket\WebSocketServer;
use Surf\Session\Driver\Redis;
use Surf\Session\SessionManager;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Server;

/**
 * Class Application
 * @package Surf
 */
class ApplicationTest extends TestCase
{
    /**
     *
     */
    public function testContainer()
    {
        $app = new Application();
        $container = $app->getContainer();
        $this->assertTrue($container instanceof Container);
        $this->assertFalse($app->has('session'));
    }

    /**
     *
     */
    public function testContainerInElement()
    {
        //$this->expectException('Swoole\Exception');
        $app = new Application();
        $element = $app->get('dispatcher');
        $this->assertTrue(isset($element));
    }

    /**
     *
     */
    public function testGetRoute()
    {
        $app = new Application();

        $path = '/test';
        $app->addGet($path, function () {});
        $routerData = $app['router']->getData();
        $this->assertArrayHasKey($path, $routerData[0]['GET']);
    }

    /**
     *
     */
    public function testPostRoute()
    {
        $app = new Application();

        $path = '/test';
        $app->addPost($path, function () {});
        $routerData = $app['router']->getData();
        $this->assertArrayHasKey($path, $routerData[0]['POST']);
    }

    /**
     *
     */
    public function testPutRoute()
    {
        $app = new Application();

        $path = '/test';
        $app->addPut($path, function () {});
        $routerData = $app['router']->getData();
        $this->assertArrayHasKey($path, $routerData[0]['PUT']);
    }

    /**
     *
     */
    public function testDeleteRoute()
    {
        $app = new Application();

        $path = '/test';
        $app->addDelete($path, function () {});
        $routerData = $app['router']->getData();
        $this->assertArrayHasKey($path, $routerData[0]['DELETE']);
    }

    /**
     *
     */
    public function testHeadRoute()
    {
        $app = new Application();

        $path = '/test';
        $app->addHead($path, function () {});
        $routerData = $app['router']->getData();
        $this->assertArrayHasKey($path, $routerData[0]['HEAD']);
    }

    /**
     *
     */
    public function testPatchRoute()
    {
        $app = new Application();

        $path = '/test';
        $app->addPatch($path, function () {});
        $routerData = $app['router']->getData();
        $this->assertArrayHasKey($path, $routerData[0]['PATCH']);
    }

    public function testGroupRoute()
    {
        $app = new Application();

        $path = '/test';
        $app->addGroup($path, function (RouteCollector $router) {
            $router->get('/name', function() {
                return "group";
            });
        });
        $routerData = $app['router']->getData();
        $this->assertArrayHasKey($path . '/name', $routerData[0]['GET']);
    }

    /**
     *
     */
    public function testTcpRoute()
    {
        $app = new Application();
        $path = 'test.name';
        $class = TestTcpController::class;
        $app->addProtocol($path, $class);

        $tcpRoute = $app->get('tcp_router');

        $this->assertEquals($class, $tcpRoute->get($path));
    }

    /**
     *
     */
    public function testTcpGroupRoute()
    {
        $app = new Application();
        $app->addProtocolGroup('user.', function(ProtocolCollector $collector) {
            $collector->add('name', function() {});
            $collector->add('list', function() {});
        });

        /**
         * @var $tcpRoute ProtocolCollector
         */
        $tcpRoute = $app->get('tcp_router');
        $this->assertTrue($tcpRoute->has('user.name'));
        $this->assertTrue($tcpRoute->has('user.list'));
    }


    public function testPool()
    {
        $config = require __DIR__ . '/../../examples/config.php';
        if (getenv('TRAVIS')) {
            $config['database']['default']['host'] = '127.0.0.1';
            $config['database']['default']['password'] = '';
        }
        $app = new Application(dirname(dirname(__DIR__)), [
            'app.config' => $config
        ]);
        $app->register(new PoolServiceProvider());

        $pool = $app->get('pool');
        $this->assertTrue($pool->pop('database.default') instanceof Connection);
    }

    /**
     *
     */
    public function testPoolRecycle()
    {
        $config = require __DIR__ . '/../../examples/config.php';
        if (getenv('TRAVIS')) {
            $config['database']['default']['host'] = '127.0.0.1';
            $config['database']['default']['password'] = '';
        }
        $app = new Application(dirname(dirname(__DIR__)), [
            'app.config' => $config
        ]);
        $app->register(new PoolServiceProvider());

        $pool = $app->get('pool');
        $connection = $pool->pop('database.default');
        $connection->ping();
        $this->assertTrue($connection->close() === true);
    }


    /**
     *
     */
    public function testPoolQuery()
    {
        $config = require __DIR__ . '/../../examples/config.php';
        if (getenv('TRAVIS')) {
            $config['database']['default']['host'] = '127.0.0.1';
            $config['database']['default']['password'] = '';
        }
        $app = new Application(dirname(dirname(__DIR__)), [
            'app.config' => $config
        ]);
        $app->register(new PoolServiceProvider());

        $pool = $app->get('pool');
        $connection = $pool->pop('database.default');
        $all = $connection->select('SELECT * FROM `user`');
        $this->assertTrue(is_array($all));
        $this->assertNotEmpty($all);
    }

    public function testEvent()
    {
        $app = new Application(dirname(dirname(__DIR__)), [
            'app.config' => require __DIR__ . '/../../examples/config.php'
        ]);
        $app->addGet('/', function () {
            return "Hello world";
        });
        $app->boot();

        $request = new Request();
        $request->server['path_info'] = '/';
        $request->server['request_method'] = 'GET';
        /**
         * @var $event GetResponseEvent
         */
        $event = $app['dispatcher']->dispatch(Events::REQUEST, new GetResponseEvent($request));

        $this->assertTrue(isset($event->getRequest()->attributes));

        $this->assertInternalType('bool', $event->hasResponse());

        $this->assertInternalType('null', $event->getResponse());
    }

    /**
     *
     */
    public function testProtocol()
    {
        $protocol = new JsonProtocol();

        $body = [
            'name' => 'meShell',
            'age'  => 18,
            'job' => 'engineer'
        ];
        $message = json_encode($body);
        $hex = pack('A64NA*', "name=user.name;format=json", strlen($message), $message);
        $protocol->unpack(1, $hex);

        $this->assertInternalType('bool', $protocol->finish(1));
        $this->assertArrayHasKey('name', $protocol->body(1));
        $this->assertEquals(strlen($message), $protocol->getLength(1));
        $this->assertEquals('user.name', $protocol->protocol(1));
        $protocol->clean(1);
    }

    /**
     *
     */
    public function testHttp()
    {
        try {
            $app = new Application();
            $server = new HttpServer($app->getContainer(), [
                'port' => 9527,
                'setting' => [
                    'daemonize' => 1
                ],
            ]);
            $swooleServer = $server->getServer();
            $server->onStart($swooleServer);
            $server->onManagerStart($swooleServer);
            $server->onWorkerStart($swooleServer, 1);
            $this->assertTrue($server instanceof HttpServer);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Swoole\Exception);
        }
    }

    /**
     *
     */
    public function testTcp()
    {
        try {
            $app = new Application();
            $server = new TcpServer($app->getContainer(), [
                'port' => 9537,
                'setting' => [
                    'daemonize' => 1
                ],
            ]);
            $swooleServer = $server->getServer();
            $server->onStart($swooleServer);
            $server->onManagerStart($swooleServer);
            $server->onWorkerStart($swooleServer, 1);
            $this->assertTrue($server instanceof TcpServer);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Swoole\Exception);
        }
    }

    /**
     *
     */
    public function testWebSocket()
    {
        try {
            $app = new Application();
            $server = new WebSocketServer($app->getContainer(), [
                'port' => 9547,
                'setting' => [
                    'daemonize' => 1
                ],
            ]);
            $swooleServer = $server->getServer();
            $server->onStart($swooleServer);
            $server->onManagerStart($swooleServer);
            $server->onWorkerStart($swooleServer, 1);
            $this->assertTrue($server instanceof WebSocketServer);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Swoole\Exception);
        }
    }

    /**
     * @dataProvider dataNotFoundProvider
     * @param $pathInfo
     */
    public function testHttpNotFoundException($pathInfo)
    {
        $app = new Application(dirname(dirname(__DIR__)), [
            'app.config' => require __DIR__ . '/../../examples/config.php'
        ]);
        $app->addGet('/exception', function () {
            return "Hello world";
        });
        $app->boot();

        $request = new Request();
        $request->server['path_info'] = $pathInfo;
        $request->server['request_method'] = 'GET';
        /**
         * @var $event GetResponseEvent
         */
        try {
            $event = $app['dispatcher']->dispatch(Events::REQUEST, new GetResponseEvent($request));
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof NotFoundHttpException);
        }
    }

    /**
     * @return array
     */
    public function dataNotFoundProvider()
    {
        return [
            ['/'],
            ['/index.html']
        ];
    }

    public function testHttpMethodNotAllowException()
    {
        $app = new Application(dirname(dirname(__DIR__)), [
            'app.config' => require __DIR__ . '/../../examples/config.php'
        ]);
        $app->addPost('/', function () {
            return "Hello world";
        });
        $app->boot();

        $request = new Request();
        $request->server['path_info'] = '/';
        $request->server['request_method'] = 'GET';
        /**
         * @var $event GetResponseEvent
         */
        try {
            $event = $app['dispatcher']->dispatch(Events::REQUEST, new GetResponseEvent($request));
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof MethodNotAllowedException);
        }
    }

    /**
     *
     */
    public function testHttpSessionContainer()
    {
        $app = new Application(dirname(dirname(__DIR__)), [
            'app.config' => require __DIR__ . '/../../examples/config.php'
        ]);
        $app->register(new SessionServiceProvider());

        $this->assertTrue(is_callable($app->get('session')));
    }

    /**
     * @dataProvider sessionDataProvider
     */
    public function testHttpSession($url, $callback, $sessionId = null)
    {
        $app = new Application(__DIR__ . '/../../examples/document_root', [
            'app.config' => require __DIR__ . '/../../examples/config.php'
        ]);
        $app->register(new PoolServiceProvider());
        $app->register(new SessionServiceProvider());
        $app->addGet($url, $callback);
        $app->boot();
        /**
         * @var $kernel HttpKernel
         */
        $kernel = $app->get('http.kernel');
        $request = new Request();
        $request->server['path_info'] = $url;
        $request->server['request_method'] = 'GET';
        $request->server['request_time'] = time();
        if ($sessionId) {
            $request->cookie['SURF_SESSION_ID'] = $sessionId;
        }
        try {
            $kernel->handle($request, new Response());
        } catch (\Exception $e) {

        }
        $this->assertTrue($request->session instanceof SessionManager);

        /**
         * @var $session SessionManager
         */
        $session = $request->session;

        $this->assertEquals('SURF_SESSION_ID', $session->getName());

        $this->assertInternalType('string', $session->getSessionId());
        $this->assertInternalType('int', $session->getExpire());
        $this->assertEquals('cookie', $session->getMode());
    }

    /**
     * @return array
     */
    public function sessionDataProvider()
    {
        return [
            ['/', function() {return "Hello world";}, null],
            ['/index', TestController::class . ':index', null],
            ['/index.php', null, null],
            ['/session', TestController::class . ':index', '73A165C0549842BD'],
        ];
    }

    public function testSessionWrite()
    {
        $app = new Application(__DIR__ . '/../../examples/document_root', [
            'app.config' => require __DIR__ . '/../../examples/config.php'
        ]);
        $app->register(new PoolServiceProvider());
        $app->register(new SessionServiceProvider());
        $app->addGet('/session', TestController::class . ':session');
        $app->boot();
        /**
         * @var $kernel HttpKernel
         */
        $kernel = $app->get('http.kernel');
        $request = new Request();
        $request->server['path_info'] = '/session';
        $request->server['request_method'] = 'GET';
        $request->server['request_time'] = time();
        $request->cookie['SURF_SESSION_ID'] = 'ccbf8a05b7826ec63c4d191168';
        try {
            $kernel->handle($request, new Response());
        } catch (\Exception $e) {

        }
        /**
         * @var $session SessionManager
         */
        $session = $request->session;
        $this->assertTrue($session->has('TEST_SESSION'));
    }

    public function testSessionRead()
    {
        $app = new Application(__DIR__ . '/../../examples/document_root', [
            'app.config' => require __DIR__ . '/../../examples/config.php'
        ]);
        $app->register(new PoolServiceProvider());
        $app->register(new SessionServiceProvider());
        $app->addGet('/session', TestController::class . ':session');
        $app->boot();
        /**
         * @var $kernel HttpKernel
         */
        $kernel = $app->get('http.kernel');
        $request = new Request();
        $request->server['path_info'] = '/session';
        $request->server['request_method'] = 'GET';
        $request->server['request_time'] = time();
        $request->cookie['SURF_SESSION_ID'] = 'ccbf8a05b7826ec63c4d191168';
        try {
            $kernel->handle($request, new Response());
        } catch (\Exception $e) {

        }
        /**
         * @var $session SessionManager
         */
        $session = $request->session;

        $this->assertEquals('Hello Session', $session->get('TEST_SESSION'));
    }

    /**
     *
     */
    public function testProtocolController()
    {
        $config = require __DIR__ . '/../../examples/config.php';
        $config['protocol'] = JsonProtocol::class;
        $config['server'] = 'tcp';
        $app = new Application(__DIR__ . '/../../examples/document_root', [
            'app.config' => $config
        ]);


        $app->addProtocol('user.name', TestTcpController::class . ':name');
        /**
         * @var $server TcpServer
         */
        $server = $app->get('server');
        $message = json_encode([
            'name' => 'meShell',
            'age'  => 18,
            'job' => 'engineer'
        ]);
        $swooleServer = $server->getServer();
        $server->onWorkerStart($swooleServer, 1);
        $hex = pack('A64NA*', "name=user.name;format=json", strlen($message), $message);
        $response = $server->handle(1, $hex);
        $this->assertInternalType('string', $response);

        $this->assertEquals('my name is meShell, my age is 18, My job is an engineer', $response);
    }

    public function testSessionOptions()
    {
        $config = require __DIR__ . '/../../examples/config.php';
        $config['session'] = [
            'name' => 'SURF_SESSION_NAME',
            'expire' => 14400,
            'mode' => 'header'
        ];
        $app = new Application(__DIR__ . '/../../examples/document_root', [
            'app.config' => $config
        ]);
        $app->register(new PoolServiceProvider());
        $app->register(new SessionServiceProvider());
        $app->addGet('/index', TestController::class . ':index');
        $app->boot();
        /**
         * @var $kernel HttpKernel
         */
        $kernel = $app->get('http.kernel');
        $request = new Request();
        $request->server['path_info'] = '/session';
        $request->server['request_method'] = 'GET';
        $request->server['request_time'] = time();
        try {
            $kernel->handle($request, new Response());
        } catch (\Exception $e) {

        }
        /**
         * @var $session SessionManager
         */
        $session = $request->session;

        $this->assertEquals('SURF_SESSION_NAME', $session->getName());
        $this->assertEquals(14400, $session->getExpire());
        $this->assertEquals('header', $session->getMode());
    }

    public function testSessionRedis()
    {
        $config = require __DIR__ . '/../../examples/config.php';
        $config['session'] = [
            'name' => 'SURF_SESSION_NAME',
            'expire' => 14400,
            'mode' => 'header',
            'driver' => 'redis'
        ];
        $app = new Application(__DIR__ . '/../../examples/document_root', [
            'app.config' => $config
        ]);
        $app['redis'] = function() {
            $redis = new \Redis();
            $redis->connect('127.0.0.1', 6379);
            return $redis;
        };
        $app->register(new PoolServiceProvider());
        $app->register(new SessionServiceProvider());
        $app->addGet('/session', TestController::class . ':session');
        $app->boot();
        /**
         * @var $kernel HttpKernel
         */
        $kernel = $app->get('http.kernel');
        $request = new Request();
        $request->server['path_info'] = '/session';
        $request->server['request_method'] = 'GET';
        $request->server['request_time'] = time();
        $request->cookie['SURF_SESSION_ID'] = 'ccbf8a05b7826ec63c4d191168';
        try {
            $kernel->handle($request, new Response());
        } catch (\Exception $e) {

        }
        /**
         * @var $session SessionManager
         */
        $session = $request->session;

        $this->assertTrue($session->getDriver() instanceof Redis);

        $session->read();
        $this->assertInternalType('bool', $session->has('TEST_SESSION'));
    }

    public function testCache()
    {
        $config = require __DIR__ . '/../../examples/config.php';
        $config['cache'] = [
            'default' => [
                'driver' => 'redis',
                'host' => '127.0.0.1',
                'prefix' => 'SURF_',
                'expire' => 7200
            ],
        ];
        $app = new Application(__DIR__ . '/../../examples/document_root', [
            'app.config' => $config
        ]);
        $app->register(new CacheServiceProvider());

        /**
         * @var $cache CacheManager
         */
        $cache = $app->get('cache.manager');
        $this->assertTrue($cache->connection('default') instanceof \Surf\Cache\Driver\Redis);
    }

    public function testCacheUse()
    {
        $config = require __DIR__ . '/../../examples/config.php';
        $config['cache'] = [
            'default' => [
                'driver' => 'redis',
                'host' => '127.0.0.1',
                'prefix' => 'SURF:',
                'expire' => 7200
            ],
        ];
        $app = new Application(__DIR__ . '/../../examples/document_root', [
            'app.config' => $config
        ]);
        $app->register(new CacheServiceProvider());

        /**
         * @var $cache CacheManager
         */
        $cache = $app->get('cache.manager');

        /**
         * @var $redis \Surf\Cache\Driver\Redis
         */
        $redis = $cache->connection('default');

        $key = 'TEST:USE';
        $this->assertTrue($redis->set($key, 'HELLO'));

        $this->assertTrue($redis->get($key) === 'HELLO');

        $this->assertTrue($redis->exists($key) == 1);

        $this->assertInternalType('int', $redis->delete($key));
    }

    public function testPoolCache()
    {
        $config = require __DIR__ . '/../../examples/config.php';
        $config['cache'] = [
            'default' => [
                'driver' => 'redis',
                'host' => '127.0.0.1',
                'prefix' => 'SURF:',
                'expire' => 7200
            ],
        ];
        $config['pool'] = array_merge($config['pool'], [
            'cache' => [
                'default' => [
                    'start_number' => 10
                ]
            ],
        ]);
        $app = new Application(__DIR__ . '/../../examples/document_root', [
            'app.config' => $config
        ]);
        $app->register(new CacheServiceProvider());

        $app->register(new PoolServiceProvider());

        /**
         * @var $manager PoolManager
         */
        $manager = $app->get('pool');

        /**
         * @var $redis CacheConnectionPool
         */
        $redis = $manager->pop('cache.default');
        $redis->ping();

        $this->assertTrue($redis instanceof CacheConnectionPool);
    }

    public function testPoolException()
    {
        $config = require __DIR__ . '/../../examples/config.php';
        $config['cache'] = [
            'default' => [
                'driver' => 'redis',
                'host' => '127.0.0.1',
                'prefix' => 'SURF:',
                'expire' => 7200
            ],
        ];
        $config['pool'] = array_merge($config['pool'], [
            'cache' => [
                'default' => [
                    'start_number' => 1,
                    'max_number'   => 1,
                ]
            ],
        ]);
        $app = new Application(__DIR__ . '/../../examples/document_root', [
            'app.config' => $config
        ]);
        $app->register(new CacheServiceProvider());

        $app->register(new PoolServiceProvider());

        /**
         * @var $manager PoolManager
         */
        $manager = $app->get('pool');

        try {
            $notFound = $manager->pop('cache.not_found');
        } catch (NotFoundPoolException | MaxWaitException | MaxOutException $e) {
            $this->assertTrue($e instanceof NotFoundPoolException);
        }

        $redis = $app->get('cache.manager')->factory('default');

        try {
            $manager->push('cache.default', new CacheConnectionPool($manager, $redis));
        } catch (MaxOutException $e) {
            $this->assertTrue($e instanceof MaxOutException);
        }
        try {
            $redis = $manager->pop('cache.default');
            $redisTwo = $manager->pop('cache.default');
        } catch (MaxWaitException | NotFoundPoolException | MaxOutException $e) {
            $this->assertTrue($e instanceof MaxWaitException);
        }
    }

    public function testCustomPool()
    {
        $config = require __DIR__ . '/../../examples/config.php';
        $config['pool'] = array_merge($config['pool'], [
            'custom' => [
                'default' => [
                    'callback' => function() {
                        return new \stdClass();
                    },
                    'start_number' => 1,
                    'max_number'   => 1,
                ],
                0 => [],
                'empty' => [],
            ],
        ]);
        $app = new Application(__DIR__ . '/../../examples/document_root', [
            'app.config' => $config
        ]);
        $app->register(new PoolServiceProvider());

        /**
         * @var $manager PoolManager
         */
        $manager = $app->get('pool');

        try {
            $custom = $manager->pop('custom.default');
        } catch (NotFoundPoolException | MaxWaitException | MaxOutException $e) {
            $this->assertTrue($e instanceof NotFoundPoolException);
        }
        $custom->ping();
        $this->assertTrue($custom instanceof ConnectionPool);
    }
}
