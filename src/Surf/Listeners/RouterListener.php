<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/2/27
 * Time: 17:22
 */

namespace Surf\Listeners;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Surf\Event\GetResponseEvent;
use Surf\Exception\MethodNotAllowedException;
use Surf\Exception\NotFoundHttpException;
use Surf\Server\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RouterListener implements EventSubscriberInterface
{
    private $router = null;

    private $routeDispatcher = null;

    private $documentRoot = null;
    /**
     * RouterListener constructor.
     * @param RouteCollector $router
     * @param Dispatcher $routeDispatcher
     */
    public function __construct(RouteCollector $router, Dispatcher $routeDispatcher = null, string $documentRoot = null)
    {
        $this->router = $router;

        $this->routeDispatcher = $routeDispatcher ?? new Dispatcher\GroupCountBased($router->getData());

        $this->documentRoot = $documentRoot;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $pathInfo = $request->server['path_info'];
        if (false !== $pos = strpos($pathInfo, '?')) {
            $pathInfo = substr($pathInfo, 0, $pos);
        }
        $extension = pathinfo($pathInfo, PATHINFO_EXTENSION);
        $requestMethod = $request->server['request_method'];
        $attributes = [];
        if (empty($extension)) {
            $routeInfo = $this->routeDispatcher->dispatch($requestMethod, rawurldecode($pathInfo));
            switch ($routeInfo[0]) {
                case Dispatcher::NOT_FOUND:
                    $message = sprintf(
                            'No route found for "%s %s"',
                            $request->server['request_method'],
                            $request->server['path_info']
                        );
                    throw new NotFoundHttpException($message);
                    break;
                case Dispatcher::METHOD_NOT_ALLOWED:
                    $message = sprintf(
                        'No route found for "%s %s": Method Not Allowed (Allow: %s)',
                        $requestMethod,
                        $pathInfo,
                        implode(', ', $routeInfo[1])
                    );
                    throw new MethodNotAllowedException($message);
                    break;
                case Dispatcher::FOUND:
                    $attributes['_file']       = false;
                    $attributes['_controller'] = $routeInfo[1];
                    $attributes['_vars']       = $routeInfo[2];
                    break;
            }
        } else {
            $file = rtrim($this->documentRoot, '/\\') . DIRECTORY_SEPARATOR . ltrim($request->server['path_info'], '/\\');
            if (!file_exists($file)) {
                $message = sprintf(
                        'No file found for "%s %s"',
                        $request->server['request_method'],
                        $request->server['path_info']
                    );
                throw new NotFoundHttpException($message);
            }
            $attributes['_file']       = true;
            $attributes['_controller'] = $file;
            $attributes['_vars']       = null;
        }
        $request->attributes = $attributes;
    }
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        // TODO: Implement getSubscribedEvents() method.
        return [
            Events::REQUEST => [['onKernelRequest', 32]]
        ];
    }
}