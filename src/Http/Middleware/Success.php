<?php

namespace JDS\Framework\Http\Middleware;

use JDS\Framework\Http\Request;
use JDS\Framework\Http\Response;

class Success implements MiddlewareInterface
{

	public function process(Request $request, RequestHandlerInterface $requestHandler): Response
	{
		return new Response('It was a success! it worked!!', 200);
	}
}