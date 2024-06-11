<?php

namespace JDS\Http\Middleware;

use JDS\Http\Request;
use JDS\Http\Response;

interface MiddlewareInterface
{
	public function process(Request $request, RequestHandlerInterface $requestHandler): Response;
}