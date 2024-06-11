<?php

namespace JDS\Framework\Http\Middleware;

use JDS\Framework\Http\Middleware\MiddlewareInterface;
use JDS\Framework\Http\Request;
use JDS\Framework\Http\Response;
use JDS\Framework\Routing\RouterInterface;
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