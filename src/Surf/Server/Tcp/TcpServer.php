<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/1
 * Time: 21:18
 */

namespace Surf\Server\Tcp;

use Surf\Mvc\Controller\TcpController;
use Surf\Server\Server;
use Swoole\Server as SwooleServer;

class TcpServer extends Server
{

    /**
     * @var null|ProtocolInterface
     */
    protected $protocol = null;

    /**
     * @var null|ProtocolCollector
     */
    protected $protocolCollector = null;

    /**
     * @return mixed|void
     */
    protected function init()
    {
        // TODO: Implement bootstrap() method.
        $this->server = new SwooleServer(
            $this->defaultConfig['host'],
            $this->defaultConfig['port']
        );
    }

    /**
     *
     */
    protected function listen()
    {
        // TODO: Implement listen() method.
        $this->server->on('receive', [$this, 'receive']);
    }


    /**
     * @param SwooleServer $server
     * @param int $fd
     * @param int $reactorId
     * @param string $data
     */
    public function receive(SwooleServer $server, int $fd, int $reactorId, string $data)
    {
        if (!is_object($this->protocol)) {
            return $server->send($fd, 'ok, But undefined protocol');
        }
        list($response, $close) = $this->handle($fd, $data, $server->worker_id);
        if ($response) {
            $server->send($fd, $response);
        }
        $close && $server->close($fd);
    }

    /**
     * @param int $fd
     * @param string $data
     * @return array
     */
    public function handle(int $fd, string $data, int $workerId = 0)
    {
        $this->protocol->unpack($fd, $data);
        if ($this->protocol->finish($fd)) {
            $protocol = $this->protocol->protocol($fd);
            if (empty($protocol)) {
                return [ 'ok, Protocol parse failed', false ];
            }
            if (empty($protocol = $this->protocolCollector->get($protocol))) {
                return [ 'ok, Protocol collector undefined', false ];
            }
            if (is_callable($protocol)) {
                $callback = $protocol;
            } else {
                $class = $protocol;
                $action = 'index';
                if (strpos($protocol, ':') !== false) {
                    list($class, $action) = explode(':', $protocol);
                }
                $instance = new $class($this->container, $workerId);
                $callback = [$instance, $action];
            }
            $content = call_user_func($callback, $this->protocol->body($fd), $fd);
            $this->protocol->clean($fd);
            $close = false;
            if (isset($instance) && $instance instanceof TcpController) {
                $close = $instance->isClose();
            }
            return [$content,  $close];
        }
        return [null, false];
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
        if (isset($this->defaultConfig['protocol'])) {
            $this->protocol = new $this->defaultConfig['protocol']($workerId, $server->setting['dispatch_mode'] ?? 2);
        }
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
    public function connect(SwooleServer $server, int $fd, int $reactorId)
    {

    }

    /**
     * @param SwooleServer $server
     * @param int $fd
     * @param int $reactorId
     */
    public function close(SwooleServer $server, int $fd, int $reactorId)
    {

    }
}
