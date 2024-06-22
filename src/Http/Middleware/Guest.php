<?php

namespace JDS\Http\Middleware;

use JDS\Http\RedirectResponse;
use JDS\Http\Request;
use JDS\Http\Response;
use JDS\Session\SessionInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Guest implements MiddlewareInterface
{
	public function __construct(private SessionInterface $session)
	{
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function process(Request $request, RequestHandlerInterface $requestHandler): Response
	{
		$this->session->start();

		if ($this->session->isAuthenticated()) {

			return new RedirectResponse($requestHandler->getContainer()->get('routePath') . '/dashboard');
		}

		return $requestHandler->handle($request);
	}
}


