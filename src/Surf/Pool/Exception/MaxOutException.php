<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/3/6
 * Time: 12:01
 */
namespace Surf\Pool\Exception;

use Throwable;

class MaxOutException extends PoolException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
