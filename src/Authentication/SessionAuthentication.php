<?php

namespace JDS\Authentication;

use JDS\Framework\Session\Session;
use JDS\Framework\Session\SessionInterface;

class SessionAuthentication implements SessionAuthInterface
{
	private AuthUserInterface $user;

	public function __construct(
		private AuthRepositoryInterface $authRepository,
		private SessionInterface $session
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

		// log the user in
		$this->session->set(Session::AUTH_KEY, $user->getAuthId());

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
}


