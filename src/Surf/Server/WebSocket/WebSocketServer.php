<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/1
 * Time: 21:19
 */

namespace Surf\Server\WebSocket;

use Surf\Mvc\Controller\WebSocketController;
use Surf\Server\Server;
use Surf\Server\Tcp\ProtocolCollector;
use Swoole\Http\Request;
use Swoole\Server as SwooleServer;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as SwooleWebSocketServer;

class WebSocketServer extends Server
{
    /**
     * @var null|ProtocolCollector
     */
    protected $protocolCollector = null;

    protected function init()
    {
        // TODO: Implement bootstrap() method.
        $this->server = new SwooleWebSocketServer(
            $this->defaultConfig['host'],
            $this->defaultConfig['port']
        );
    }

    protected function listen()
    {
        // TODO: Implement listen() method.

        $this->server->on('open', [$this, 'open']);

        $this->server->on('message', [$this, 'message']);

        $isBindHttp = $this->defaultConfig['is_open_http'] ?? false;
        if ($isBindHttp) {
            $kernel = $this->container->get('http.kernel');
            $this->server->on('request', [$kernel, 'handle']);
        }
    }

    /**
     * @param SwooleWebSocketServer $server
     * @param Request $request
     */
    public function open(SwooleWebSocketServer $server, Request $request)
    {

    }

    /**
     * 消息接收强制数据格式为json, {"name": "pro", "body": {}}
     * @param SwooleWebSocketServer $server
     * @param Frame $frame
     * @return bool
     */
    public function message(SwooleWebSocketServer $server, Frame $frame)
    {
        if (empty($frame->data)) {
            return $server->push($frame->fd, json_encode([
                'code' => 500, 'message' => 'data parse failed', 'body' => []
            ]));
        }
        $json = json_decode($frame->data);
        if (empty($json->protocol) || !($protocol = $this->protocolCollector->get($json->protocol))) {
            return $server->push($frame->fd, json_encode([
                'code' => 501, 'message' => 'protocol parse failed', 'body' => []
            ]));
        }
        if (is_callable($protocol)) {
            $callback = $protocol;
        } else {
            $class = $protocol;
            $action = 'index';
            if (strpos($protocol, ':') !== false) {
                list($class, $action) = explode(':', $protocol);
            }

            $instance = new $class($this->container, $server->worker_id);
            if ($instance instanceof WebSocketController) {
                $instance->setFrame($frame);
            }
            $callback = [$instance, $action];
        }
        $content = call_user_func($callback, $json->body);
        return $server->push($frame->fd, json_encode([
            'code' => 0, 'message' => 'success', 'body' => $content
        ]));
    }

    /**
     * @param SwooleServer $server
     * @return mixed
     */
    protected function start(\Swoole\Server $server)
    {
        // TODO: Implement start() method.
    }

    /**
     * @param SwooleServer $server
     * @param int $workerId
     * @return mixed
     */
    protected function workerStart(\Swoole\Server $server, int $workerId)
    {
        // TODO: Implement workerStart() method.
        $this->protocolCollector = $this->container->get('tcp_router');
    }

    /**
     * @param SwooleServer $server
     * @return mixed
     */
    protected function managerStart(\Swoole\Server $server)
    {
        // TODO: Implement managerStart() method.
    }

    /**
     * @param SwooleServer $server
     * @param int $fd
     * @param int $reactorId
     */
    protected function close(\Swoole\Server $server, int $fd, int $reactorId)
    {

    }

    /**
     *
     */
    protected function connect(\Swoole\Server $server, int $fd, int $reactorId)
    {
        // TODO: Implement connect() method.
    }
}
