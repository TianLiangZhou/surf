<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/3/6
 * Time: 11:33
 */
namespace Surf\Pool\Connections;

use Surf\Pool\Connection;



class DatabaseConnectionPool extends Connection
{

    /**
     *
     */
    public function ping()
    {
        // TODO: Implement ping() method.
        $this->select('SELECT 1');
    }
}