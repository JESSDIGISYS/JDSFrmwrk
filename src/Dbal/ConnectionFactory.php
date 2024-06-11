<?php

namespace JDS\Framework\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class ConnectionFactory
{

	public function __construct(private array $databaseUrl)
	{
	}

	public function create(): Connection
	{
		return DriverManager::getConnection($this->databaseUrl);
//			[
//				'driver' => 'pdo_mysql',
//				'user' => 'frmwrk@localhost',
//				'password' => 'thisiscool',
//				'host' => 'localhost',
//				'port' => 3306]);
	}
}