<?php

namespace JDS\Http\Middleware;

use Firebase\JWT\JWT;
use JDS\Http\RedirectResponse;
use JDS\Http\Request;
use JDS\Http\Response;
use JDS\Session\SessionInterface;

class Authenticate implements MiddlewareInterface
{
	private string $jwtKey;

	public function __construct(
		private SessionInterface $session
	)
	{
	}

	public function process(Request $request, RequestHandlerInterface $requestHandler): Response
	{
		$this->session->start();

		if (!$this->session->isAuthenticated()) {
			$this->session->setFlash('error', 'Please sign in first!');

			return new RedirectResponse($requestHandler->getContainer()->get('routePath') . '/login');
		}
		$this->setJwtKey($request->getServerVariable('JWTSECRET'));

		$this->session->set('jwttoken', $this->generateJWT($this->session->get('auth_id'), $request)) ;

		return $requestHandler->handle($request);
	}

	private function generateJWT(string $user_id, Request $request) {
		$payload = [
			'iss' => $request->getServerVariable('HTTP_HOST'),
			'aud' => $request->getServerVariable('SERVER_NAME'),
			'iat' => time(), // Issued At Time
			'nbf' => time(), // Not valid Before
			'exp' => time() + (60 * 60 * 24 * 5), // expire after iat (5 days)
			'data' => [
				'user_id' => $user_id,
			]
		];

		return JWT::encode($payload, $this->jwtKey, 'HS256');
	}

	public function setJwtKey(string $jwtKey): void
	{
		$this->jwtKey = $jwtKey;
	}
}


