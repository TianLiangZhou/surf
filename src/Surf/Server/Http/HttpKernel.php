<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/5
 * Time: 15:32
 */

namespace Surf\Server\Http;

use Pimple\Psr11\Container;
use Slim\Http\Response as SlimResponse;
use Zend\Diactoros\Response as ZendResponse;
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
        if ($finishResponse instanceof SlimResponse) {

        }
        if ($finishResponse instanceof ZendResponse) {

        }
        $response->header('Content-Type', 'text/html; charset=UTF-8');
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
            $extension = pathinfo($attributes['_controller'], PATHINFO_EXTENSION);
            if (strtolower($extension) == 'php') {
                ob_start();
                require $attributes['_controller'];
                $response = ob_get_clean();
            } else {
                $response = file_get_contents($attributes['_controller']);
            }
        } else {
            $vars = $attributes['_vars'] ?? [];
            if (!is_array($vars)) $vars = [$request, $vars];
            else array_unshift($vars, $request);
            if (is_callable($attributes['_controller'])) {
                $callback = $attributes['_controller'];
            } else {
                list($controller, $method) = explode(':', $attributes['_controller']);
                if (!class_exists($controller)) {
                    throw new \Exception("Can not find the controller '$controller'");
                }
                $hash = md5($controller);
                if (isset($this->controllers[$hash])) {
                    $class = new $this->controllers[$hash];
                } else {
                    $class = new $controller($this->container);
                }
                if (!method_exists($class, $method)) {
                    throw new \Exception("Can not find the controller method '$method'");
                }
                $callback = [$class, $method];
            }
            try {
                $response = call_user_func_array($callback, $vars);
            } catch (\Exception $e) {
                throw $e;
            }
        }
        return $response;
    }
}