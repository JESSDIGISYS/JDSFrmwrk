<?php

namespace JDS\Framework\Http\Middleware;

use JDS\Framework\Http\Request;
use JDS\Framework\Http\Response;

interface RequestHandlerInterface
{
	public function handle(Request $request): Response;
}