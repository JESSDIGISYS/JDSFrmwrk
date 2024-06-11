<?php

namespace JDS\Http\Middleware;

use JDS\Http\Request;
use JDS\Http\Response;

class Dummy implements MiddlewareInterface
{

	public function process(Request $request, RequestHandlerInterface $requestHandler): Response
	{
		return $requestHandler->handle($request);
	}
}

