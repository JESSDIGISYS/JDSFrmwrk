<?php

namespace JDS\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Statement;

abstract class AbstractDatabaseHelper
{
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
}

