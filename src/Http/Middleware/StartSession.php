<?php

namespace JDS\Http\Middleware;

use JDS\Http\Request;
use JDS\Http\Response;
use JDS\Session\SessionInterface;

class StartSession implements MiddlewareInterface
{
	public function __construct(
		private SessionInterface $session,
		private string $apiPrefix = '/api/'
	)
	{
	}

	public function process(Request $request, RequestHandlerInterface $requestHandler): Response
	{
		if (!str_starts_with($request->getPathInfo(), $this->apiPrefix)) {
			$this->session->start();

			$request->setSession($this->session);
		}

		return $requestHandler->handle($request);
	}
}

