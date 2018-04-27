<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang01
 * Date: 2018/4/26
 * Time: 15:12
 */

namespace Surf\Examples;


use Surf\Ticker\Ticker;

class HeartbeatTicker extends Ticker
{

    public function execute()
    {
        // TODO: Implement execute() method.
        echo "Heartbeat interval " . $this->interval;
    }
}