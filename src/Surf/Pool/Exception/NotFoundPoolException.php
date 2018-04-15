<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/3/6
 * Time: 13:24
 */

namespace Surf\Pool\Exception;

use Throwable;

class NotFoundPoolException extends PoolException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
