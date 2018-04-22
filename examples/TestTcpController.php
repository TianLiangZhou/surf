<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/10
 * Time: 9:59
 */

namespace Surf\Examples;

use Surf\Mvc\Controller\TcpController;
use Surf\Task\PushTaskHandle;

class TestTcpController extends TcpController
{
    /**
     * @param $body
     * @return string
     */
    public function name($body)
    {
        return "my name is " . $body['name'] .  ", my age is " . $body['age'] . ", My job is an " . $body['job'] ;
    }

    /**
     * @return string
     */
    public function taskTest()
    {
        $taskId = $this->task('push all message worker' . $this->workerId, PushTaskHandle::class);
        //$status = $this->syncTask('sync push all message', PushTaskHandle::class);
        //var_dump($status);
        return "task push id:" . $taskId . ", workId:" . $this->workerId;
    }
}
