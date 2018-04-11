<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/1
 * Time: 15:54
 */

namespace Surf\Test;

use PHPUnit\Framework\TestCase;
use Pimple\Psr11\Container;
use Surf\Application;
use Surf\Event\GetResponseEvent;
use Surf\Examples\TestTcpController;
use Surf\Exception\MethodNotAllowedException;
use Surf\Exception\NotFoundHttpException;
use Surf\Exception\ServerNotFoundException;
use Surf\Pool\Connection;
use Surf\Provider\PoolServiceProvider;
use Surf\Server\Events;
use Surf\Server\Http\HttpServer;
use Surf\Server\Tcp\Protocol\JsonProtocol;
use Surf\Server\Tcp\TcpServer;
use Surf\Server\WebSocket\WebSocketServer;
use Swoole\Http\Request;

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
        $this->assertTrue((new Application())->getContainer() instanceof Container);
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
    public function testRoute()
    {
        $app = new Application();

        $path = '/test';
        $app->addGet($path, function () {
        });
        $routerData = $app['router']->getData();
        $this->assertArrayHasKey($path, $routerData[0]['GET']);
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
            //$server->run();
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
            //$server->run();
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
            //$server->run();
            $this->assertTrue($server instanceof WebSocketServer);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Swoole\Exception);
        }
    }

    public function testHttpNotFoundException()
    {
        $app = new Application(dirname(dirname(__DIR__)), [
            'app.config' => require __DIR__ . '/../../examples/config.php'
        ]);
        $app->addGet('/exception', function () {
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
            $this->assertTrue($e instanceof NotFoundHttpException);
        }
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
}
