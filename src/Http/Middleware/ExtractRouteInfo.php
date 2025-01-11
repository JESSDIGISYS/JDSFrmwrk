<?php

namespace JDS\Http\Middleware;


use Exception;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use JDS\Http\HttpException;
use JDS\Http\HttpRequestMethodException;
use JDS\Http\Request;
use JDS\Http\Response;
use League\Container\Container;
use function FastRoute\simpleDispatcher;

class ExtractRouteInfo implements MiddlewareInterface
{
	public function __construct(private readonly array $routes, private readonly string $routePath)
	{
	}

	/**
	 * @throws HttpException
	 * @throws HttpRequestMethodException
	 * @throws Exception
	 */
	public function process(Request $request, RequestHandlerInterface $requestHandler): Response
	{
		// create a dispatcher
		$dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) {

			foreach ($this->routes as $route) {
                $routePath = '/' . trim($this->routePath, '/');
                $route[1] = $routePath . '/' . trim($route[1], '/');
				$routeCollector->addRoute(...$route);
			}
		});

		// dispatch a URI, to obtain the route info
		$routeInfo = $dispatcher->dispatch(
			$request->getMethod(),
			$request->getPathInfo(),
		);

		switch ($routeInfo[0]) {
			case Dispatcher::FOUND:
				// set $request->routeHandler
				$request->setRouteHandler($routeInfo[1]);

				// set $request->routeHandlerArgs
				$request->setRouteHandlerArgs($routeInfo[2]);

				// inject route middleware on handler
                if (is_array($routeInfo[1]) && isset($routeInfo[1][2])) {
                    $requestHandler->injectMiddleware($routeInfo[1][2]);
                }
				break;

			case Dispatcher::METHOD_NOT_ALLOWED:
				$allowedMethods = implode(', ', $routeInfo[1]);
				$e = new HttpRequestMethodException("The allowed methods are $allowedMethods");
				$e->setStatusCode(405);
				throw $e;

			default:
				$e = new HttpException("Not found");
				$e->setStatusCode(404);
				throw $e;
		}

		return $requestHandler->handle($request);
	}
}

