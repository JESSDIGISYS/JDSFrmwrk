<?php

namespace JDS\Http\Middleware;

use JDS\Http\Middleware\MiddlewareInterface;
use JDS\Http\Request;
use JDS\Http\Response;

class AuthenticateRoles implements MiddlewareInterface
{
	public function __construct(private array $routes)
	{
	}

	public function process(Request $request, RequestHandlerInterface $requestHandler): Response
	{
		foreach ($this->routes as $route) {
			dd($route);
		}
	}
}