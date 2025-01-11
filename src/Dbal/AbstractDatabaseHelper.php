<?php

namespace JDS\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Statement;

abstract class AbstractDatabaseHelper
{
    protected DataMapper $dataMapper;

    /**
     * @throws Exception
     */
    public function bind(Statement $statement, string $parameter, $value, $type = null): void {
        switch (is_null($type)) {
            case is_int($value):
                $type = ParameterType::INTEGER;
                break;

            case is_bool($value):
                $type = ParameterType::BOOLEAN;
                break;

            case is_null($value):
                $type = ParameterType::NULL;
                break;

            default:
                $type = ParameterType::STRING;
        }
        $statement->bindValue($parameter, $value, $type);
    }

    public function checkTableExists(Connection $connection, string $database, string $table): bool
    {
        try {
            // Assuming $pdo is your PDO connection
            $sql = "SELECT count(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :databaseName AND TABLE_NAME = :tableName;";

            $stmt = $connection->prepare($sql);
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

    public function getDataMapper(): DataMapper
    {
        return $this->dataMapper;
    }

    public function setDataMapper(DataMapper $dataMapper): void
    {
        $this->dataMapper = $dataMapper;
    }

}