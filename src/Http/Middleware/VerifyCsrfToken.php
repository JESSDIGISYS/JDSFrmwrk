<?php

namespace JDS\Framework\Http\Middleware;

use JDS\Framework\Http\Middleware\MiddlewareInterface;
use JDS\Framework\Http\Request;
use JDS\Framework\Http\Response;
use JDS\Framework\Http\TokenMismatchException;

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