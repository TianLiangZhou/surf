<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/4
 * Time: 16:02
 */
namespace Surf\Server\Tcp\Protocol;

use Surf\Server\Tcp\Protocol;

class JsonProtocol extends Protocol
{

    /**
     * @param int $fd
     * @param string $package
     */
    public function unpack(int $fd, string $package):void
    {
        // TODO: Implement unpack() method.
        $this->makeUnpack($fd, $package);
    }

    /**
     * @param int $fd
     * @return bool
     */
    public function finish(int $fd): bool
    {
        // TODO: Implement isFinish() method.

        return $this->package['finish'][$fd] ?? false;
    }

    /**
     * @param int $fd
     * @return string
     */
    public function protocol(int $fd): string
    {
        // TODO: Implement protocol() method.
        $header = $this->getHeader($fd);
        if (empty($header)) {
            return '';
        }
        $split = explode(';', $header);
        $name = $split[0];
        if (strpos($split[0], '=') !== false) {
            $name = explode('=', $split[0])[1];
        }
        return $name;
    }

    /**
     * @return mixed
     */
    public function body(int $fd)
    {
        // TODO: Implement body() method.
        return json_decode($this->getBody($fd), true);
    }

    /**
     * @param int $fd
     * @return mixed|void
     */
    public function clean(int $fd)
    {
        $this->makeClean($fd);
    }
}