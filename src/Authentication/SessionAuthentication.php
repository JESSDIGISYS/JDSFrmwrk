<?php

namespace JDS\Authentication;

use Firebase\JWT\JWT;
use JDS\Session\Session;
use JDS\Session\SessionInterface;

class SessionAuthentication implements SessionAuthInterface
{
	private AuthUserInterface $user;

	private string $accessToken;

	private string $refreshToken;

	public function __construct(
		private AuthRepositoryInterface $authRepository,
		private SessionInterface $session,
		private string $jwtSecretKey
	)
	{
	}

	public function authenticate(string $email, string $password): bool
	{
		// query db for user using email
		$user = $this->authRepository->findByEmail($email);

		if (!$user) {
			return false;
		}

		// does the hashed user pw match the hash of the attempted password
		if (!password_verify($password, $user->getPassword())) {
			// return false
			return false;
		}
		// if yes, log the user in
		$this->login($user);

		// return true
		return true;
	}

	public function login(AuthUserInterface $user): void
	{
		// start a session
		$this->session->start();
		$issuedAt = time();
		// todo PUT JWT HERE
		$commonPayload = [
			'iss' => $this->session->get('SERVER_NAME'),
			'aud' => $this->session->get('HTTP_HOST'),
		];

		$accessPayload = [
			'iat' => $issuedAt,
			'exp' => $issuedAt + (15 + 60),
			'token_type' => 'access'
		];

		$this->accessToken = JWT::encode($accessPayload, $this->jwtSecretKey);

		$refreshPayload = array_merge($commonPayload, [
			'iat' => $issuedAt,
			'exp' => $issuedAt + (60 * 60 * 24 * 14),
			'token_type' => 'refresh',
			'userid' => $user->getAuthId(),
			'email' => $user->getEmail()
		]);
		$this->refreshToken = JWT::encode($refreshPayload, $this->jwtSecretKey);

		// log the user in
		$this->session->set(Session::AUTH_KEY, $user->getAuthId());
		$this->session->set(Session::ACCESS_TOKEN, $this->accessToken);
		$this->session->set(Session::REFRESH_TOKEN, $this->refreshToken);

		// set the user
		$this->user = $user;
	}

	public function logout()
	{
		$this->session->remove(Session::AUTH_KEY);
	}

	public function getUser(): AuthUserInterface
	{
		return $this->user;
	}

	public function getAccessToken(): string
	{
		return $this->accessToken;
	}

	public function getRefreshToken(): string
	{
		return $this->refreshToken;
	}
}


