<?php

namespace JDS\Console\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use JDS\Console\ConsoleException;
use JDS\Dbal\GenerateNewId;
use PDOException;
use Throwable;

class MigrateDatabase implements CommandInterface
{
    private string $name = 'database:migrations:migrate';

    public function __construct(
        private Connection $connection,
        private string $migrationsPath
    )
    {
    }

    /**
     * @throws Throwable
     * @throws Exception
     */
    public function execute(array $params = []): int
    {

        if (array_key_exists('up', $params)) {
            $msg = 'Executing: ' . $this->name . ' "Up" ';
            if (is_numeric($params['up'])) {
                $msg .= 'for migration number ' . $params['up'];
            }
            echo $msg . PHP_EOL;
        } elseif (array_key_exists('down', $params)) {
            $msg = 'Executing: ' . $this->name . ' "Down" ';
            if (is_numeric($params['down'])) {
                $msg .= 'for migration number ' . $params['down'];
            }
            echo $msg . PHP_EOL;
        } else {
            throw new ConsoleException("Invalid parameters. Please use: --up or --down! Can also be --up=(integer) or --down=(integer) to specify the migration number to run. Example: --up-1 would run m00001_name.php and --down-1 would run m00001_name.php");
        }

        $execute = 0;
        // migrations up
        // create a migrations table SQL if table not already in existence
        $this->createMigrationsTable();
        if (array_key_exists('up', $params)) {
            if (is_numeric($params['up'])) {
                $up = $params['up'];
                $found = false;
                $migrationFiles = $this->getMigrationFiles();
                foreach ($migrationFiles as $migration) {
                    $mig_number = (int)substr($migration, 1, strpos($migration, '_') - 1);
                    if ($mig_number == $up) {
                        $this->executeMigration('up', $migration, $this->getConnection());
                        $this->insertMigration($migration);
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    echo 'Migration ' . $up . ' successfully applied!' . PHP_EOL;
                } else {
                    echo 'Migration ' . $up . ' not found! No Migrations were applied...' . PHP_EOL;
                }
            } else {


                // get $appliedMigrations which are already in the database.migrations table
                // since we are also going for the down as well as the up
                // we'll add a flag to be able to order in the proper order
                $appliedMigrations = $this->getAppliedMigrations();

                // get the $migrationFiles from the migrations folder
                $migrationFiles = $this->getMigrationFiles();

                // get the migrations to apply. i.e. they are in $migrationFiles but not in
                // $appliedMigrations
                $migrationsToApply = array_diff($migrationFiles, $appliedMigrations);

                // create SQL for any migrations which have not been run ... i.e. which are not in the
                // database
                if (count($migrationsToApply) > 0) {
                    // loop through migrations in ascending order
                    foreach ($migrationsToApply as $migration) {

                        // call the up method
                        $up = false;
                        if ($params['up']) {
                            $up = true;
                            $upCalled = true;
                            $this->executeMigration('up', $migration, $this->getConnection());
                            // add migration to database
                            $this->insertMigration($migration);
                        }
                    }
                } else {
                    echo "All migrations have been applied..." . PHP_EOL;
                }
            }
            // migrations down
        } elseif (array_key_exists('down', $params)) {
            if (is_numeric($params['down'])) {
                $down = $params['down'];
                $found = false;
                $migrationFiles = $this->getMigrationFiles();
                if (count($migrationFiles) > 0) {
                    foreach ($migrationFiles as $migration) {
                        $mig_number = (int)substr($migration, 1, strpos($migration, '_') - 1);
                        if ($mig_number == $down) {
                            $this->executeMigration('down', $migration, $this->getConnection());
                            $found = true;
                            break;
                        }
                    }
                } else {
                    echo "There are no migrations to roll back..." . PHP_EOL;
                }
                if ($found) {
                    echo 'Migration ' . $down . ' successfully rolled back!' . PHP_EOL;
                } else {
                    echo 'Migration ' . $down . ' not found! No Migrations were rolled back...' . PHP_EOL;
                }
            } else {
                // get migrations applied
                $appliedMigrations = $this->getAppliedMigrations();
                // loop through migrations in descending order
                $mig_count = 0;
                if (count($appliedMigrations) > 0) {
                    foreach (array_reverse($appliedMigrations, true) as $migration) {
                        if (file_exists($this->migrationsPath . '/' . $migration)) {
                            // call the down method
                            $this->executeMigration('down', $migration, $this->getConnection());
                            // remove the migration from database
                            $this->removeMigration($migration);
                            $mig_count++;
                        } else {
                            $this->removeMigration($migration);
                            echo 'Migration file ' . $migration . ' not found! Removing from migrations' . PHP_EOL;
                        }
                    }
                } else {
                    echo "There are no migrations to roll back..." . PHP_EOL;
                }
                if ($mig_count >= count($appliedMigrations)) {
                    $this->connection->executeQuery('TRUNCATE migrations;');
                }
            }
        }
        return 0;
    }

    /**
     * @throws Exception
     */
    private function insertMigration($migration): void
    {
        $sql = "INSERT INTO migrations (migration) VALUES (:mg);";
        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':mg', $migration);

        $stmt->executeStatement();
    }

    /**
     * @throws Exception
     */
    private function removeMigration($migration): void
    {
        $sql = "DELETE FROM migrations WHERE migration = :mg;";
        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':mg', $migration);

        $stmt->executeStatement();
    }

    private function getMigrationFiles(): array
    {
        $migrationFiles = scandir($this->migrationsPath);
        $filterdFiles = array_filter($migrationFiles, function ($file) {
            return !in_array($file, ['.', '..', '.gitignore', 'm00000_template.php']);
        });
        return $filterdFiles;
    }

    /**
     * Retrieves the list of applied migrations from the database.
     *
     * @return array The list of applied migrations.
     * @throws Exception
     */
    private function getAppliedMigrations(): array
    {
        $sql = 'SELECT migration FROM migrations ORDER BY migration ASC;';

        return $this->connection->executeQuery($sql)->fetchFirstColumn();
    }

    /**
     * @throws Throwable
     * @throws Exception
     */
    private function createMigrationsTable(): void
    {
        // schema manager
        $schemaManager = $this->connection->createSchemaManager();

        // if tables does NOT exist, create it
        if (!$schemaManager->tablesExist(['migrations'])) {
            // schema
            $schema = new Schema();
            try {

                // create table
                $table = $schema->createTable('migrations')->addOption('engine', 'InnoDB');

                // id
                $table->addColumn('id', Types::INTEGER, ['length' => 12, 'unsigned' => true, 'autoincrement' =>
                    true]);

                // migration name
                $table->addColumn('migration', Types::STRING, ['length' => 60]);

                // datetime
                $table->addColumn('created_at', Types::DATETIME_IMMUTABLE, ['default' => 'CURRENT_TIMESTAMP']);

                // primary key
                $table->setPrimaryKey(['id']);

                $sqlArray = $schema->toSql($this->connection->getDatabasePlatform());
                if (count($sqlArray) > 0) {
                    $this->connection->executeQuery($sqlArray[0]);
                    echo 'migrations table created' . PHP_EOL;
                }
            } catch (Throwable $throwable) {
                throw $throwable;
            }
        } else {
            echo '<< migrations table already exists >>' . PHP_EOL . 'Create Migrations Table Skipped!' . PHP_EOL;
        }

    }

    /**
     * @throws ConsoleException
     */
    private function executeMigration(string $direction, string $migration, Connection $connection): void
    {
        $migrationObject = require $this->migrationsPath . '/' . $migration;

        try {
            $migrationObject->$direction($migration, $this->getConnection());
        } catch (PDOException $pe) {
            switch ($pe->errorInfo[1]) {
                case 1062:
                    throw new ConsoleException('Duplicate entry for ' . $migration . '  SQLSTATE[' . $pe->errorInfo[0] . ']: ' . $pe->errorInfo[1] . ' ' . $pe->errorInfo[2]);
                case 1451:
                    throw new ConsoleException('Cannot delete or update a parent row: a foreign key constraint fails ' . $migration . '  SQLSTATE[' . $pe->errorInfo[0] . ']: ' . $pe->errorInfo[1] . ' ' . $pe->errorInfo[2]);
                case 1049:
                    throw new ConsoleException('Unknown database ' . $migration . '  SQLSTATE[' . $pe->errorInfo[0] . ']: ' . $pe->errorInfo[1] . ' ' . $pe->errorInfo[2]);
                case 1045:
                    throw new ConsoleException('Access denied for user ' . $migration . '  SQLSTATE[' . $pe->errorInfo[0] . ']: ' . $pe->errorInfo[1] . ' ' . $pe->errorInfo[2]);
                default:
                    throw new ConsoleException($pe->getMessage() . '  SQLSTATE[' . $pe->errorInfo[0] . ']: ' . $pe->errorInfo[1] . ' ' . $pe->errorInfo[2]);
            }
        }
    }

    private function getConnection(): Connection
    {
        return $this->connection;
    }
}
