<?php

namespace JDS\Dbal;



use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PDO;

class ConnectionFactory
{

	public function __construct(private array $databaseUrl)
	{
	}

	public function create(): Connection
	{
		return DriverManager::getConnection($this->databaseUrl);
	}

    public function bind($value, $type = null): int {
        $type = match (is_null($type)) {
            is_int($value) => PDO::PARAM_INT,
            is_bool($value) => PDO::PARAM_BOOL,
            is_null($value) => PDO::PARAM_NULL,
            default => PDO::PARAM_STR,
        };
       return $type;
    }
}