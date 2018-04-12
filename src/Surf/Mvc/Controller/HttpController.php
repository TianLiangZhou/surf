<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/4
 * Time: 11:20
 */

namespace Surf\Mvc\Controller;

use Surf\Mvc\Controller;
use Swoole\Http\Request;

class HttpController extends Controller
{
    /**
     * @var Request
     */
    protected $request = null;

    /**
     * @return null
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param null $request
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }
}
