<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/9
 * Time: 9:51
 */

namespace Surf\Server\Tcp;


/**
 * Class Protocol
 * @package Surf\Server\Tcp
 */
abstract class Protocol implements ProtocolInterface
{

    /**
     * @var string 默认解包格式, 包头，包长，包体
     */
    protected $unpackFormat = 'A64header/Nlen/A*data';

    /**
     * @var
     */
    protected $fd = [];

    /**
     * @var array
     */
    protected $package = [
        'header' => [],
        'body'   => [],
        'body_length' => [],
        'length' => [],
        'is_unpack' => [],
        'finish' => [],
    ];

    /**
     * @var int
     */
    protected $workerId = 0;

    /**
     * @var int 分发模式
     */
    protected $dispatchMode = 2;
    /**
     * Protocol constructor.
     */
    public function __construct(int $workerId = 0, int $dispatchMode = 2)
    {
        $this->workerId = $workerId;

        $this->dispatchMode = $dispatchMode;
    }

    /**
     * @param int $fd
     * @param string $package
     */
    public function makeUnpack(int $fd, string $package)
    {
        if (!$this->isUnpack($fd)) {
            $this->package['is_unpack'][$fd] = true;
            $unPackage = unpack($this->unpackFormat, $package);
            if (isset($unPackage['header']) && $unPackage['header']) {
                $this->setHeader($fd, $unPackage['header']);
            }
            if (isset($unPackage['len']) && $unPackage['len']) {
                $this->setLength($fd, (int) $unPackage['len']);
            }

            if (isset($unPackage['data']) && $unPackage['data']) {
                $this->setBody($fd, $unPackage['data']);
            }
        }
        if ($this->isUnpack($fd)) {
            if ($this->getBodyLength($fd) >= $this->getLength($fd)) {
                $this->package['finish'][$fd] = true;
            } else {
                $this->setBody($fd, $package);
            }
        }
    }

    /**
     * @param int $fd
     * @return bool
     */
    public function isUnpack(int $fd)
    {
        return $this->package['is_unpack'][$fd] ?? false;
    }

    /**
     * @param int $fd
     * @param string $body
     */
    public function setBody(int $fd, string $body)
    {
        $this->package['body'][$fd] = isset($this->package['body'][$fd])
            ? $this->package['body'][$fd] . $body
            : $body;
        $this->package['body_length'][$fd] = strlen($this->package['body'][$fd]);
    }

    /**
     * @param int $fd
     * @return string
     */
    public function getBody(int $fd)
    {
        return $this->package['body'][$fd] ?? '';
    }

    /**
     * @param int $fd
     * @return int
     */
    public function getBodyLength(int $fd)
    {
        return $this->package['body_length'][$fd] ?? 0;
    }

    /**
     * @param int $fd
     * @param int $len
     */
    public function setLength(int $fd, int $len)
    {
        $this->package['length'][$fd] = $len;
    }

    /**
     * @param int $fd
     * @return int
     */
    public function getLength(int $fd)
    {
        return $this->package['length'][$fd] ?? 0;
    }

    /**
     * @param int $fd
     * @param string $header
     */
    public function setHeader(int $fd, string $header)
    {
        $this->package['header'][$fd] = $header;
    }

    /**
     * @param int $fd
     * @return string
     */
    public function getHeader(int $fd)
    {
        return $this->package['header'][$fd] ?? '';
    }

    /**
     * @param int $fd
     */
    protected function makeClean(int $fd)
    {
        $packageKey = ['header', 'body', 'body_length', 'length', 'is_unpack', 'finish' ];
        foreach ($packageKey as $name) {
            if (isset($this->package[$name][$fd])) {
                unset($this->package[$name][$fd]);
            }
        }
    }
}