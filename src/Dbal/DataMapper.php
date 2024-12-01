<?php

namespace JDS\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use JDS\Dbal\Events\PostPersist;
use JDS\EventDispatcher\EventDispatcher;

class DataMapper
{
	public function __construct(
		private readonly Connection $connection,
		private readonly EventDispatcher $eventDispatcher,
        private readonly GenerateNewId $generateNewId
	)
	{
	}

	public function getConnection(): Connection
	{
		return $this->connection;
	}

    public function newId(int $length = 12, bool $symbol = false): string
    {
        return $this->generateNewId->getNewId($length, $symbol);
    }

    /**
     * @throws Exception
     */
    public function save(Entity $subject): int|string|null
	{
		// dispatch post persist event
		$this->eventDispatcher->dispatch(new PostPersist($subject));

		// return last insert id
		return $this->connection->lastInsertId();
	}
}

