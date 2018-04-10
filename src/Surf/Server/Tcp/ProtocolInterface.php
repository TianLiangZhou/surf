<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/9
 * Time: 9:51
 */

namespace Surf\Server\Tcp;

/**
 * Interface ProtocolInterface
 * @package Surf\Server\Tcp
 */
interface ProtocolInterface
{
    /**
     * @param int $fd
     * @return bool
     */
    public function finish(int $fd): bool;

    /**
     * @param int $fd
     * @param string $package
     */
    public function unpack(int $fd, string $package): void;

    /**
     * @return string
     */
    public function protocol(int $fd): string;

    /**
     * @return mixed
     */
    public function body(int $fd);

    /**
     * @param int $fd
     * @return mixed
     */
    public function clean(int $fd);
}
