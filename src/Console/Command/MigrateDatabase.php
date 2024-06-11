<?php

namespace JDS\Framework\Console\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
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

	public function execute(array $params = []): int
	{
		echo  'Executing: ' . $this->name . PHP_EOL;
		$execute = 0;

		// create a migrations table SQL if table not already in existence
		$this->createMigrationsTable();


		// get $appliedMigrations which are already in the database.migrations table
		$appliedMigrations = $this->getAppliedMigrations();

		// get the $migrationFiles from the migrations folder
		$migrationFiles = $this->getMigrationFiles();

		// get the migrations to apply. i.e. they are in $migrationFiles but not in
		// $appliedMigrations
		$migrationsToApply = array_diff($migrationFiles, $appliedMigrations);

		$schema = new Schema();

		// create SQL for any migrations which have not been run ... i.e. which are not in the
		// database
		foreach ($migrationsToApply as $migration) {
			// require the file
			$migrationObject = require $this->migrationsPath . '/' . $migration;
			// call the up method
			$migrationObject->up($schema, $migration);

			// add migration to database
			$this->insertMigration($migration);
		}

		// execute the SQL query
		$sqlArray = $schema->toSql($this->connection->getDatabasePlatform());

		foreach ($sqlArray as $sql) {
			$this->connection->executeQuery($sql);
			$execute += 1;
		}
		if ($execute > 0) {
			echo 'SQL has been executed!' . PHP_EOL;
		} else {
			echo 'SQL has NOT been executed!' . PHP_EOL;
		}
		echo 'Executing MigrateDatabase command...' . PHP_EOL;
		return 0;
	}

	private function insertMigration($migration): void
	{
		try {
			$sql = "INSERT INTO migrations (migration) VALUES (:mg);";
			$stmt = $this->connection->prepare($sql);

			$stmt->bindValue(':mg', $migration);

			$stmt->executeStatement();
		} catch (Throwable $throwable) {
			throw $throwable;
		}
	}

	private function getMigrationFiles(): array
	{
		$migrationFiles = scandir($this->migrationsPath);
		$filterdFiles = array_filter($migrationFiles, function ($file) {
			return !in_array($file, ['.', '..', '.gitignore', 'm00000_test.php']);
		});
		return $filterdFiles;
	}

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
				$table = $schema->createTable('migrations');

				// id
				$table->addColumn('id', Types::INTEGER, ['length' => 12, 'unsigned' => true, 'autoincrement' =>
					true]);

				// migration name
				$table->addColumn('migration', Types::STRING, ['length' => 40]);

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
}