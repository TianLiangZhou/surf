<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/14
 * Time: 21:19
 */

namespace Surf\Cache;


abstract class Driver implements DriverInterface
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * @var int
     */
    protected $expire = 86400;

    /**
     * Driver constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;

        if (isset($this->options['prefix'])) {
            $this->prefix = $options['prefix'];
        }

        if (isset($this->options['expire'])) {
            $this->expire = (int) $options['expire'];
        }
    }
}