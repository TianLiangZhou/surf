<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/4
 * Time: 11:21
 */

namespace Surf\Mvc\Controller;

use Surf\Mvc\Controller;
use Swoole\WebSocket\Frame;

class WebSocketController extends Controller
{
    /**
     * @var null | Frame
     */
    protected $frame = null;

    /**
     * @param null|Frame $frame
     */
    public function setFrame(Frame $frame)
    {
        $this->frame = $frame;
    }
}
