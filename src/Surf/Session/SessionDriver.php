<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/12
 * Time: 10:14
 */

namespace Surf\Session;


abstract class SessionDriver
{
    protected $options = [];

    const RANDOM_BYTE = 13;

    public function __construct(array $options = [])
    {

    }


    /**
     * @return string
     */
    protected function generateId()
    {
        return bin2hex(random_bytes(static::RANDOM_BYTE));
    }

    public function regenerateId()
    {

    }

}