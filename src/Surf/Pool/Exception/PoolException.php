<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/15
 * Time: 10:31
 */

namespace Surf\Pool\Exception;


use Throwable;

class PoolException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}