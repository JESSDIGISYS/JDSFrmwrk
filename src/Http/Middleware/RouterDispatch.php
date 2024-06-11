<?php

namespace JDS\Http\Middleware;

use JDS\Http\Middleware\MiddlewareInterface;
use JDS\Http\Request;
use JDS\Http\Response;
use JDS\Routing\RouterInterface;
use Psr\Container\ContainerInterface;

class RouterDispatch implements MiddlewareInterface
{

	public function __construct(
		private RouterInterface $router,
		private ContainerInterface $container
	)
	{
	}

	public function process(Request $request, RequestHandlerInterface $requestHandler): Response
	{
		[$routeHandler, $vars] = $this->router->dispatch($request, $this->container);

		$response = call_user_func_array($routeHandler, $vars);

		return $response;
	}
}