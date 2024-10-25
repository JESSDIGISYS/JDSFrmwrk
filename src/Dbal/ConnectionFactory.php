<?php

namespace JDS\Dbal;



use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class ConnectionFactory
{

	public function __construct(private readonly array $databaseUrl)
	{
	}

	public function create(): Connection
	{
		return DriverManager::getConnection($this->databaseUrl);
	}
}
