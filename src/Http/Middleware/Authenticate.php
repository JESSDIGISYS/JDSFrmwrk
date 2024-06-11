<?php

namespace JDS\Http\Middleware;

use JDS\Http\RedirectResponse;
use JDS\Http\Request;
use JDS\Http\Response;
use JDS\Session\SessionInterface;

class Authenticate implements MiddlewareInterface
{
	public function __construct(private SessionInterface $session)
	{
	}

	public function process(Request $request, RequestHandlerInterface $requestHandler): Response
	{
		$this->session->start();

		if (!$this->session->isAuthenticated()) {
			$this->session->setFlash('error', 'Please sign in first!');

			return new RedirectResponse('/login');
		}
		return $requestHandler->handle($request);
	}
}


