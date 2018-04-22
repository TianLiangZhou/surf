<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/5
 * Time: 15:32
 */

namespace Surf\Server\Http;

use Pimple\Psr11\Container;
use Psr\Http\Message\ResponseInterface;
use Surf\Mvc\Controller\HttpController;
use Surf\Server\Http\Cookie\CookieAttributes;
use Surf\Server\Http\Cookie\Cookies;
use Surf\Server\RedisConstant;
use Surf\Session\SessionManager;
use Surf\Event\GetResponseEvent;
use Surf\Exception\MethodNotAllowedException;
use Surf\Exception\NotFoundHttpException;
use Surf\Server\Events;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zend\Diactoros\Response\HtmlResponse;

class HttpKernel
{
    /**
     * @var null|EventDispatcherInterface
     */
    private $dispatcher = null;

    /**
     * @var null|Container
     */
    private $container = null;

    /**
     * HttpKernel constructor.
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher, Container $container)
    {
        $this->dispatcher = $dispatcher;

        $this->container = $container;
    }

    /**
     * @param Request $request
     * @param Response $sourceResponse
     */
    public function handle(Request $request, Response $sourceResponse)
    {
        $cookies = new Cookies($request->cookie ?? []);
        try {
            $response = $this->handleResponse($request, $cookies);
        } catch (NotFoundHttpException | MethodNotAllowedException $e) {
            $response = new HtmlResponse($e->getMessage(), $e->getStatusCode());
        } catch (\Exception $e) {
            $response = new HtmlResponse($e->getMessage(), 500);
        }
        $this->finishResponse(
            $sourceResponse,
            $response,
            $cookies,
            $request->server['request_time']
        );
        return true;
    }

    /**
     * @param Response $sourceResponse
     * @param ResponseInterface $response
     * @param Cookies $cookies
     * @param int $timestamps 当前请求时间
     */
    private function finishResponse(
        Response $sourceResponse,
        ResponseInterface $response,
        Cookies $cookies,
        int $timestamps
    ) {
        foreach ($cookies->getResponseCookies() as $name => $cookieAttributes) {
            /**
             * @var $cookieAttributes CookieAttributes
             */
            $sourceResponse->cookie(
                $name,
                $cookieAttributes->getValue(),
                $cookieAttributes->getExpire() + $timestamps,
                $cookieAttributes->getPath(),
                $cookieAttributes->getDomain(),
                $cookieAttributes->isSecure(),
                $cookieAttributes->isHttpOnly()
            );
        }
        foreach ($response->getHeaders() as $name => $value) {
            $sourceResponse->header($name, implode(',', $value));
        }
        $sourceResponse->status($response->getStatusCode());
        $sourceResponse->end((string) $response->getBody());
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     * @throws \Exception
     */
    private function handleResponse(Request $request, Cookies $cookies)
    {
        $this->registerSession($request);
        /**
         * @var $event GetResponseEvent
         */
        $event = $this->dispatcher->dispatch(Events::REQUEST, new GetResponseEvent($request));
        if ($event->hasResponse()) {
            $response = $event->getResponse();
        } else {
            $attributes = $request->attributes;
            $response = null;
            if ($attributes['_file']) {
                $response = $this->getFileResponse($attributes['_controller'], $request, $cookies);
            } else {
                $response = $this->getControllerResponse($attributes, $request, $cookies);
            }
        }
        if ($response === null) {
            throw new \RuntimeException("Response must to string or implementation 'ResponseInterface' interface");
        }
        if (!($response instanceof ResponseInterface)) {
            $response = new HtmlResponse($response);
        }
        return $this->registerSessionShutdown($request, $response, $cookies);
    }

    /**
     * @param string $file
     * @param Request $request
     * @param Cookies $cookies
     * @return string
     */
    private function getFileResponse(string $file, Request $request, Cookies $cookies)
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        if (strtolower($extension) == 'php') {
            ob_start();
            require $file;
            $response = ob_get_clean();
        } else {
            $response = file_get_contents($file);
        }
        return $response;
    }

    /**
     * @param array $attributes
     * @param Request $request
     * @param Cookies $cookies
     * @return mixed
     * @throws \Exception
     */
    private function getControllerResponse(array $attributes, Request $request, Cookies $cookies)
    {
        if (is_callable($attributes['_controller'])) {
            $callback = $attributes['_controller'];
        } else {
            list($controller, $method) = explode(':', $attributes['_controller']);
            if (!class_exists($controller)) {
                throw new \Exception("Can not find the controller '$controller'");
            }
            $workerId = 0;
            if ($this->container->has('redis') && isset($request->fd)) {
                $redis = $this->container->get('redis');
                $workerId = (int) $redis->hGet(RedisConstant::FULL_FD_WORKER, $request->fd);
            }
            $class = new $controller($this->container, $workerId);
            if (!method_exists($class, $method)) {
                throw new \Exception("Can not find the controller method '$method'");
            }
            if ($class instanceof HttpController) {
                $class->setRequest($request);
                if (property_exists($request, 'session')) {
                    $class->setSession($request->session);
                }
                $class->setCookies($cookies);
            }
            $callback = [$class, $method];
            if (method_exists($class, 'initialize')) {
                $class->initialize();
            }
        }
        try {
            $response = call_user_func(
                $callback,
                $attributes['_vars'],
                (is_array($callback) ? null : $request),
                (is_array($callback) ? null : $cookies)
            );
        } catch (\Exception $e) {
            throw $e;
        }
        return $response;
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function registerSession(Request $request)
    {
        if (!$this->container->has('session')) {
            return false;
        }
        $options = $this->container->get('app.config')['session'] ?? [];
        $cookieName = $options['name'] ?? 'SURF_SESSION_ID';
        $mode = $options['mode'] ?? 'cookie';
        if ($mode === 'header') {
            $sessionId = $request->header[$cookieName] ?? null;
        } else {
            $sessionId = $request->cookie[$cookieName] ?? null;
        }
        $sessionFactory = $this->container->get('session');

        /**
         * @var $session SessionManager
         */
        $session = $sessionFactory($this->container, $sessionId);

        $session->start();

        $request->session = $session;
        return true;
    }

    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @param Cookies $cookies
     * @return ResponseInterface $response
     */
    private function registerSessionShutdown(Request $request, ResponseInterface $response, Cookies $cookies)
    {
        if (!$this->container->has('session')) {
            return $response;
        }
        /**
         * @var $session SessionManager
         */
        $session = $request->session;
        $options = $this->container->get('app.config')['session'] ?? [];
        $mode = $options['mode'] ?? 'cookie';
        if ($mode == 'header') {
            $response = $response->withHeader($session->getName(), $session->getSessionId());
        } else {
            $cookies->set(
                CookieAttributes::create($session->getName(), $session->getSessionId(), $session->getExpire())
            );
        }
        $session->save();
        return $response;
    }
}
