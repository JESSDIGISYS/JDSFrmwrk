<?php

namespace JDS\Http\Middleware;

use JDS\Http\Request;
use JDS\Http\Response;

interface RequestHandlerInterface
{
	public function handle(Request $request): Response;
}