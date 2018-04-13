<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/13
 * Time: 16:56
 */

namespace Surf\Server\Http\Cookie;


/**
 * Class Cookies
 * @package Surf\Server\Http\Cookie
 */
class Cookies
{
    /**
     * Cookies from HTTP request
     *
     * @var array
     */
    private $requestCookies = [];

    /**
     * Cookies for HTTP response
     *
     * @var array
     */
    private $responseCookies = [];


    public function __construct(array $requestCookies = [])
    {
        $this->requestCookies = $requestCookies;
    }

    /**
     * @param string $name
     * @param string $default
     * @return string
     */
    public function get(string $name, $default = null)
    {
        return $this->requestCookies[$name] ?? $default;
    }

    /**
     * @param string $name
     * @param string $value
     * @param CookieAttributes|null $cookieAttributes
     */
    public function set(CookieAttributes $cookieAttributes)
    {
        $this->responseCookies[$cookieAttributes->getName()] = $cookieAttributes;
    }

    /**
     * @return array
     */
    public function getResponseCookies()
    {
        return $this->responseCookies;
    }
}