<?php

namespace JDS\Http\Middleware;

use JDS\Http\RedirectResponse;
use JDS\Http\Request;
use JDS\Http\Response;
use JDS\Session\SessionInterface;

class Guest implements MiddlewareInterface
{
	public function __construct(private SessionInterface $session)
	{
	}

	public function process(Request $request, RequestHandlerInterface $requestHandler): Response
	{
		$this->session->start();

		if ($this->session->isAuthenticated()) {

			return new RedirectResponse('/dashboard');
		}

		return $requestHandler->handle($request);
	}
}


