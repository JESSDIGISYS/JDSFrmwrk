<?php

namespace JDS\Framework\Authentication;

interface AuthRepositoryInterface
{
	public function findByEmail(string $email): ?AuthUserInterface;
}