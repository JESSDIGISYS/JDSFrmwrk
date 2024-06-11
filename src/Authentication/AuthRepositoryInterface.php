<?php

namespace JDS\Authentication;

interface AuthRepositoryInterface
{
	public function findByEmail(string $email): ?AuthUserInterface;
}