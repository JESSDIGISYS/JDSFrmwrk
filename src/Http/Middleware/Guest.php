<?php

namespace JDS\Framework\Http\Middleware;

use JDS\Framework\Http\RedirectResponse;
use JDS\Framework\Http\Request;
use JDS\Framework\Http\Response;
use JDS\Framework\Session\SessionInterface;

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


