<?php

namespace JDS\Http\Middleware\Jwt;


use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
use JDS\Http\Middleware\MiddlewareInterface;
use JDS\Http\Middleware\RequestHandlerInterface;
use JDS\Http\Request;
use JDS\Http\Response;

class JwtAuthenticate implements MiddlewareInterface
{
	public function __construct(private string $jwtSecretKey)
	{
	}


	public function process(Request $request, RequestHandlerInterface $requestHandler): Response
	{
		// get the authorization header
		$authHeader = $request->getServerVariable('HTTP_AUTHORIZATION');

		// return failed auth if missing
		if (!$authHeader) {
			return new Response(
				'Auth token missing',
				401,
				['WWW-Authenticate' => 'Bearer error="missing_token"']
			);
		}

		// extract the token from the authorization header
		$token = str_replace('/^Bearer\s*/', '', $authHeader);

		// try to decode
		try {
			$decoded = JWT::decode($token, new Key($this->jwtSecretKey, 'HS256'));
			// do what you want with claims then pass back to RequestHandler if decodes
			$jwt = new Jwt($this->jwtSecretKey);
			$payload = $jwt->validateToken($token);

			// return failed auth if token is invalid
			if (!$payload) {
				return new Response(
					'Invalid token',
					401,
					['www-Authenticate' => 'Bearer error="invalid_token"']
				);
			}

			// set the authenticated user in the session
			$session = $request->getSession();
			$session->set('user', $payload['user']);

			return $requestHandler->handle($request);
			// catch whatever exceptions you wanna handle individually
		} catch (ExpiredException) {
			return new Response(
				'Auth token has expired',
				401,
				['WWW-Authenticate' => 'Bearer error="expired_token"']
			);
		} catch (\UnexpectedValueException|\DomainException) {
			return new Response(
				'Auth token is invalid',
				401,
				['WWW-Authenticate' => 'Bearer error="invalid_token"']
			);
		}
 	}

	 private function saveMe() {
		 // validate the token

		 // continue processing the request
		 return $requestHandler->handle($request);

	 }
}


