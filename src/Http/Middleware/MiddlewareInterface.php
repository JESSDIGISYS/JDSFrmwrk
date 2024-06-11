<?php

namespace JDS\Framework\Http\Middleware;

use JDS\Framework\Http\Request;
use JDS\Framework\Http\Response;

interface MiddlewareInterface
{
	public function process(Request $request, RequestHandlerInterface $requestHandler): Response;
}