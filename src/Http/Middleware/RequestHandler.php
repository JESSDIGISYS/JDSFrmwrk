<?php

namespace JDS\Framework\Http\Middleware;

use JDS\Framework\Http\Request;
use JDS\Framework\Http\Response;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class RequestHandler implements RequestHandlerInterface
{
	private array $middleware = [
		ExtractRouteInfo::class,
		StartSession::class,
		VerifyCsrfToken::class,
		RouterDispatch::class
	];

	public function __construct(private ContainerInterface $container)
	{
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function handle(Request $request): Response
	{
		// if there are no middleware classes to execute, return a default response
		// a response should have been returned before the list becomes empty

		if (empty($this->middleware)) {
			return new Response("It's totally borked, mate. Contact support", 500);
		}

		// get the next middleware class to execute
		$middlewareClass = array_shift($this->middleware);

		$middleware = $this->container->get($middlewareClass);

		// create a new instance of the middleware call process on it
		$response = $middleware->process($request, $this);
		return $response;
	}

	public function injectMiddleware(array $middleware): void
	{
		array_splice($this->middleware, 0, 0, $middleware);
	}
}


