<?php

namespace JDS\Authentication;

interface AuthUserInterface
{
	public function getAuthId(): int|string;

	public function getEmail(): string;

	public function getPassword(): string;

}
