<?php
namespace CleaRest\Api;

use CleaRest\Api\Handlers\ContentTypeHandler;
use CleaRest\Api\Handlers\HttpExceptionHandler;
use CleaRest\Api\Handlers\FallbackExceptionHandler;
use CleaRest\Api\Handlers\HttpStatusCodeHandler;
use CleaRest\Api\Handlers\ValidationExceptionHandler;
use CleaRest\Api\Exceptions\RouterException;

class Router
{
    /**
     * @var Route[]
     */
    private $routes = [];

    /**
     * @var RequestHandler[]
     */
    private $handlers = [];

    public function __construct($registerDefaultHandlers = true)
    {
        // Registering default handlers
        if ($registerDefaultHandlers) {
            $this->addHandler(new ContentTypeHandler());
            $this->addHandler(new HttpStatusCodeHandler());
            $this->addHandler(new FallbackExceptionHandler());
            $this->addHandler(new ValidationExceptionHandler());
            $this->addHandler(new HttpExceptionHandler());
        }
    }

    /**
     * Returns true when call was successful
     * @param Request $request
     * @param Response $response
     * @return bool
     * @throws \Exception when not handled
     */
	public function process(Request $request, Response $response)
    {
        try {
            $method = $this->getMethodFromRequest($request);
            $caller = new ServiceController(
                $this,
                $method->getInterfaceName(),
                $method->getInterfaceMethod(),
                $method->getImplementationVersion()
            );
            $caller->call($request, $response);

            return true;
        } catch (\Exception $exception) {
            foreach ($this->handlers as $handler) {
                if ($handler->handleException($exception, $request, $response)) {
                    foreach ($this->handlers as $handler2) {
                        if ($handler2->postResponse($request, $response)) {
                            break;
                        }
                    }
                    return false;
                }
            }

            throw $exception;
        }
    }

    /**
     * @return Route[]
     */
    public function getAllRoutes()
    {
	    return $this->routes;
    }

    /**
     * @param $route
     * @return Route
     */
    public function getRoute($route)
    {
        if (!isset($this->routes[$route])) {
            $this->routes[$route] = new Route($route);
        }
        return $this->routes[$route];
    }

    /**
     * @param RequestHandler $handler
     */
    public function addHandler(RequestHandler $handler)
    {
        array_unshift($this->handlers, $handler);
    }

    /**
     * @return RequestHandler[]
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * Returns method that matches request or throws exception when no route matches or route matches but no method found
     * @param Request $request
     * @return Method
     * @throws RouterException
     */
    private function getMethodFromRequest(Request $request) {
        $routeUri = $this->getRouteFromUri($request);

        $route = null;
        foreach ($this->routes as $r) {
            $parameters = $r->match($routeUri);
            if ($parameters !== false) {
                foreach ($parameters as $name => $value) {
                    $request->setField($name, $value);
                }
                $route = $r;
                break;
            }
        }

        if ($route === null) {
            throw new RouterException(
                "Route not found",
                RouterException::ROUTE_NOT_FOUND,
                ['route' => $routeUri]
            );
        }

        $method = $route->getMethod($request->getMethod());
        if ($method === null) {
            throw new RouterException(
                "Method not allowed",
                RouterException::METHOD_NOT_ALLOWED,
                ['route' => $routeUri, 'method' => $request->getMethod()]
            );

        }

        return $method;
    }

    private function getRouteFromUri(Request $request)
    {
        $script = $request->getScriptName();
        $pattern = '/([^?]*)\??(.*)/';
        preg_match($pattern, $request->getUri(), $matches);
        $uri = $matches[1];

        $pattern = '/(.*)\\' . DIRECTORY_SEPARATOR . '.*/';
        preg_match($pattern, $script, $matches);
        $root = $matches[1];
        $route = str_replace($root, '', $uri);

        return $route;
    }
}

