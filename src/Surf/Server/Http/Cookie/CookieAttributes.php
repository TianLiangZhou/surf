<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/4/13
 * Time: 10:56
 */

namespace Surf\Server\Http\Cookie;


final class CookieAttributes
{
    /* ------------------- */
    private $name   = '';

    private $value  = '';
    /* ------------------- */


    /**
     * @var string
     */

    private $domain = '';

    private $path = '/';

    private $http_only = false;

    private $secure = false;

    /**
     * @var int
     */
    private $expire = 0;

    private $maxAge;

    /**
     * CookieAttributes constructor.
     * @param string $name
     * @param string $value
     * @param int $expire
     */
    private function __construct($name, $value, $expire = 0)
    {
        $this->name = $name;

        $this->value = $value;

        $this->expire = $expire;
    }

    /**
     * @param string $name
     * @param string $value
     * @param int $expire
     * @return CookieAttributes
     */
    public static function create(string $name, string $value, int $expire = 0): self
    {
        return new static($name, $value, $expire);
    }

    /**
     * @param string $domain
     * @return CookieAttributes
     */
    public function withDomain($domain = '')
    {
        $new = clone $this;
        $new->domain = $domain;
        return $new;
    }

    /**
     * @param string $path
     * @return CookieAttributes
     */
    public function withPath($path = '/')
    {
        $new = clone $this;
        $new->path = $path;
        return $new;
    }

    /**
     * @return CookieAttributes
     */
    public function withHttpOnly()
    {
        $new = clone $this;
        $new->http_only = true;
        return $new;
    }

    /**
     * @return CookieAttributes
     */
    public function withSecure()
    {
        $new = clone $this;
        $new->secure = true;
        return $new;
    }

    /**
     * @param int $timestamps
     * @return CookieAttributes
     */
    public function withExpire($timestamps = 0)
    {
        $new = clone $this;
        $new->expire = $timestamps;
        return $new;
    }
    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return mixed
     */
    public function getMaxAge()
    {
        return $this->maxAge;
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * @return int
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * @return bool
     */
    public function isHttpOnly(): bool
    {
        return $this->http_only;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}