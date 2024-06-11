<?php

namespace JDS\Framework\Http\Middleware;

use JDS\Framework\Http\Request;
use JDS\Framework\Http\Response;

class Dummy implements MiddlewareInterface
{

	public function process(Request $request, RequestHandlerInterface $requestHandler): Response
	{
		return $requestHandler->handle($request);
	}
}

