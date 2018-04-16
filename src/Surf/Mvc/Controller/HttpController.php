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
use Surf\Session\SessionManager;
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
     * @var null|SessionManager;
     */
    protected $session = null;

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

    /**
     * @return null|SessionManager
     */
    public function getSession(): ?SessionManager
    {
        return $this->session;
    }

    /**
     * @param null|SessionManager $session
     */
    public function setSession(?SessionManager $session): void
    {
        $this->session = $session;
    }
}
