<?php

namespace JDS\Console\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use JDS\Http\FileNotFoundException;
use Throwable;

class MigrateDatabase implements CommandInterface
{
	private string $name = 'database:migrations:migrate';

	public function __construct(
		private Connection $connection,
		private string	$migrationsPath
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
            echo  'Executing: ' . $this->name . ' "Up"' . PHP_EOL;
        } elseif (array_key_exists('down', $params)) {
            echo  'Executing: ' . $this->name . ' "Down"' . PHP_EOL;
        } else {
            echo  'Executing: ' . $this->name . PHP_EOL;
        }

		$execute = 0;
        // migrations up
        if (array_key_exists('up', $params)) {

            // create a migrations table SQL if table not already in existence
            $this->createMigrationsTable();


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
            $upCalled = false;
            // loop through migrations in ascending order
            foreach ($migrationsToApply as $migration) {
                // require the file
                if (file_exists($this->migrationsPath . '/' . $migration)) {
                    $migrationObject = require $this->migrationsPath . '/' . $migration;
                    // call the up method
                    $up = false;
                    if ($params['up']) {
                        $up = true;
                        $upCalled = true;
                        $migrationObject->up($migration, $this->getConnection());

                        // add migration to database
                        $this->insertMigration($migration);
                    }
                } else {
                    $this->removeMigration($migration);
                    throw new FileNotFoundException('Migration file not found: ' . $migration);
                }
            }
        // migrations down
        } elseif (array_key_exists('down', $params)) {
            // get migrations applied
            $appliedMigrations = $this->getAppliedMigrations();
            // loop through migrations in descending order
            foreach (array_reverse($appliedMigrations,true) as $migration) {
                if (file_exists($this->migrationsPath . '/' . $migration)) {
                    // require the file
                    $migrationObject = require $this->migrationsPath . '/' . $migration;
                    // call the down method
                    $migrationObject->down($migration, $this->getConnection());
                    // remove the migration from database
                    $this->removeMigration($migration);
                } else {
                    $this->removeMigration($migration);
                    throw new FileNotFoundException('Migration file not found: ' . $migration);
                }
            }
        }

		echo 'Executing MigrateDatabase command...' . PHP_EOL;
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
			return !in_array($file, ['.', '..', '.gitignore', 'm00000_test.php']);
		});
		return $filterdFiles;
	}

    /**
     * Retrieves the list of applied migrations from the database.
     *
     * @param bool $down Optional. When true, retrieves the migrations in descending order.
     *                    When false, retrieves them in ascending order. Default is false.
     * @return array The list of applied migrations.
     */
    private function getAppliedMigrations(): array
	{
        $sql = 'SELECT migration FROM migrations ORDER BY migration ASC;';

		$appliedMigrations = $this->connection->executeQuery($sql)->fetchFirstColumn();

		return $appliedMigrations;
	}

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
			} catch (\Throwable $throwable) {
				throw $throwable;
			}
		} else {
			echo '<< migrations table already exists >>' . PHP_EOL . 'Create Migrations Table Skipped!'	. PHP_EOL;
		}

//		$table->addForeignKeyConstraint('migrations', ['migration_id'], ['id']);
//		$table->addForeignKeyConstraint('migrations', ['migration_class'], ['id']);

	}

	private function getConnection(): Connection
	{
		return $this->connection;
	}
}
