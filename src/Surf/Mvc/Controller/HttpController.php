<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/4
 * Time: 11:20
 */

namespace Surf\Mvc\Controller;

use Surf\Mvc\Controller;
use Surf\Server\Http\Cookie\Cookies;
use Swoole\Http\Request;

class HttpController extends Controller
{
    /**
     * @var Request
     */
    protected $request = null;


    /**
     * @var Cookies
     */
    protected $cookies = null;

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

    /**
     * @return Cookies
     */
    public function getCookies(): Cookies
    {
        return $this->cookies;
    }

    /**
     * @param Cookies $cookies
     */
    public function setCookies(Cookies $cookies): void
    {
        $this->cookies = $cookies;
    }
}
