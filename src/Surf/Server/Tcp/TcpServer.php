<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/1
 * Time: 21:18
 */

namespace Surf\Server\Tcp;

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

        $this->server->on('connect', [$this, 'connect']);

        $this->server->on('receive', [$this, 'receive']);

        $this->server->on('close', [$this, 'close']);
    }


    /**
     * @param SwooleServer $server
     * @param int $fd
     * @param int $reactorId
     * @param string $data
     */
    public function receive(SwooleServer $server, int $fd, int $reactorId, string $data)
    {
        if ($this->protocol) {
            $this->protocol->unpack($fd, $data);
            if ($this->protocol->finish($fd)) {
                $protocol = $this->protocol->protocol($fd);
                if (empty($protocol)) {
                    return $server->send($fd, 'ok, Protocol parse failed');
                }
                if (empty($protocol = $this->protocolCollector->get($protocol))) {
                    return $server->send($fd, 'ok, Protocol collector undefined');
                }
                if (is_callable($protocol)) {
                    $callback = $protocol;
                } else {
                    $class = $protocol;
                    $action = 'index';
                    if (strpos($protocol, ':') !== false) {
                        list($class, $action) = explode(':', $protocol);
                    }
                    $callback = [new $class($this->container), $action];
                }
                $content = call_user_func(
                    $callback,
                    $this->protocol->body($fd),
                    $fd
                );
                $this->protocol->clean($fd);
                return $server->send($fd, $content);
            }
        } else {
            return $server->send($fd, 'ok, But undefined protocol');
        }
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
}
