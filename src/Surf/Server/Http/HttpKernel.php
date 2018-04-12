<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/5
 * Time: 15:32
 */

namespace Surf\Server\Http;

use Pimple\Psr11\Container;
use Surf\Mvc\Controller\HttpController;
use Surf\Session\SessionManager;
use Surf\Event\GetResponseEvent;
use Surf\Exception\MethodNotAllowedException;
use Surf\Exception\NotFoundHttpException;
use Surf\Server\Events;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class HttpKernel
{
    /**
     * @var null|EventDispatcherInterface
     */
    protected $dispatcher = null;

    /**
     * @var null|Container
     */
    protected $container = null;

    /**
     * @var array
     */
    protected $controllers = [];
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
     * @param Response $response
     */
    public function handle(Request $request, Response $response)
    {
        try {
            $finishResponse = $this->handleResponse($request);
        } catch (NotFoundHttpException | MethodNotAllowedException $e) {
            $finishResponse = $e->getMessage();
            $response->status($e->getStatusCode());
        } catch (\Exception $e) {
            $finishResponse = $e->getMessage();
            $response->status(500);
        }
        if (class_exists('\Psr\Http\Message\ResponseInterface')
            && $finishResponse instanceof \Psr\Http\Message\ResponseInterface) {
        } else {
            $response->header('Content-Type', 'text/html; charset=UTF-8');
        }
        $response->end($finishResponse);
        return ;
    }

    /**
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    private function handleResponse(Request $request)
    {
        $this->registerSession($request);
        /**
         * @var $event GetResponseEvent
         */
        $event = $this->dispatcher->dispatch(Events::REQUEST, new GetResponseEvent($request));
        if ($event->hasResponse()) {
            return $event->getResponse();
        }
        $attributes = $request->attributes;
        $response = null;
        if ($attributes['_file']) {
            $response = $this->getFileResponse($attributes['_controller'], $request);
        } else {
            $response = $this->getControllerResponse($attributes, $request);
        }
        return $response;
    }

    /**
     * @param $file
     * @return bool|string
     */
    private function getFileResponse($file, Request $request)
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
     * @param $attributes
     * @param Request $request
     * @return mixed|null
     * @throws \Exception
     */
    private function getControllerResponse($attributes, Request $request)
    {
        if (is_callable($attributes['_controller'])) {
            $callback = $attributes['_controller'];
        } else {
            list($controller, $method) = explode(':', $attributes['_controller']);
            if (!class_exists($controller)) {
                throw new \Exception("Can not find the controller '$controller'");
            }
            $hash = md5($controller);
            $class = $this->controllers[$hash] ?? (new $controller($this->container));
            if (!method_exists($class, $method)) {
                throw new \Exception("Can not find the controller method '$method'");
            }
            if ($class instanceof HttpController) {
                $class->setRequest($request);
            }
            if (!isset($this->controllers[$hash])) {
                $this->controllers[$hash] = $class;
            }
            $callback = [$class, $method];
            if (method_exists($class, 'initialize')) {
                $class->initialize();
            }
        }
        try {
            $response = call_user_func(
                $callback, $attributes['_vars'], (is_array($callback) ? null : $request)
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
        } elseif ($mode === 'header') {
            $sessionId = $request->cookie[$cookieName] ?? null;
        }
        $sessionFactory = $this->container->get('session');

        /**
         * @var $session SessionManager
         */
        $session = $sessionFactory($this->container, $sessionId);

        $session->start();

        $request->session = $session;
        return false;
    }
}
