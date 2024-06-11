<?php

namespace JDS\Http\Middleware;

use JDS\Http\Middleware\MiddlewareInterface;
use JDS\Http\Request;
use JDS\Http\Response;
use JDS\Http\TokenMismatchException;

class VerifyCsrfToken implements MiddlewareInterface
{

	public function process(Request $request, RequestHandlerInterface $requestHandler): Response
	{
		// proceed if not state change request
		if (!in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
			return $requestHandler->handle($request);
		}

		// retrieve the tokens
		$tokenFromSession = $request->getSession()->get("csrf_token");
		$tokenFromRequest = $request->input("_token");

		// throw an exception on mismatch
		if (!hash_equals($tokenFromSession, $tokenFromRequest)) {
			// throw an exception
			$exception = new TokenMismatchException('Your request could not be validated. Please try again.');
			$exception->setStatusCode(Response::HTTP_FORBIDDEN);
			throw $exception;
		}
		// proceed
		return $requestHandler->handle($request);
	}
}