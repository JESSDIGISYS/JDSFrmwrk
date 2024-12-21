<?php

namespace JDS\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use JDS\Dbal\Events\PostPersist;
use JDS\EventDispatcher\EventDispatcher;

class DataMapper
{
    public function __construct(
        private readonly Connection      $connection,
        private readonly EventDispatcher $eventDispatcher,
        private readonly GenerateNewId   $generateNewId
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

    /**
     * @throws \Exception
     */
    public function checkTableExists(string $database, string $table): bool
    {
        try {
            // Assuming $pdo is your PDO connection
            $sql = "SELECT count(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :databaseName AND TABLE_NAME = :tableName;";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue('databaseName', $database);
            $stmt->bindValue('tableName', $table);
            $rows = $stmt->executeQuery();

            if ($rows->fetchFirstColumn()) {
                // The table exists, you can continue your operations here.
                return true;
            } else {
                // The table does not exist.
                // You can notify the user about this and stop execution, or handle this situation in any other way that suits your work.
                return false;
            }
        } catch (Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
}

