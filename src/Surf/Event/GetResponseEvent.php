<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/27
 * Time: 16:42
 */

namespace Surf\Event;

use Swoole\Http\Request;
use Symfony\Component\EventDispatcher\Event;

class GetResponseEvent extends Event
{
    protected $response = null;

    protected $request = null;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
        $this->stopPropagation();
    }

    /**
     * @return null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return null|Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return bool
     */
    public function hasResponse()
    {
        return null !== $this->response;
    }
}
