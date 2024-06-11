<?php

namespace JDS\Console\Command;

use Doctrine\DBAL\Connection;
use Exception;

class RollbackDatabase implements CommandInterface
{
	private string $name = 'database:migrations:rollback';

	public function __construct(private Connection $connection, private string $migrationPath)
	{
	}

	public function execute(array $params = []): int
	{
		$executed = 0;
		try {
			// check to see if migrations table exists
			if ($this->checkMigrationsTable()) {

				$appliedMigrations = $this->getAppliedMigrations();

				foreach ($appliedMigrations as $migration) {
					$migrationObject = require $this->migrationPath . '/' . $migration;

					$migrationObject->down($this->connection, $migration);
					$executed += 1;
					$this->removeMigration($migration);
				}

			} else {
				Throw new Exception('Migrations table does not exist');
			}
			if ($executed > 0) {
				echo 'Migrations rolled backed!' . PHP_EOL;
			} else {
				echo 'Migrations NOT rolled backed!' . PHP_EOL;
			}
			return 0;
		} catch (\Throwable	$throwable) {
			$this->connection->rollBack();
			Throw $throwable;
		}
	}

	private function removeMigration($migration): void
	{
		$sql = "DELETE FROM migrations where migration = :mg";
		$stmt = $this->connection->prepare($sql);
		$stmt->bindValue(':mg', $migration);
		$stmt->executeStatement();

	}

	private function getAppliedMigrations(): array
	{
		$sql = 'SELECT migration FROM migrations ORDER BY migration;';

		$appliedMigrations = $this->connection->executeQuery($sql)->fetchFirstColumn();

		return $appliedMigrations;
	}

	private function checkMigrationsTable(): bool
	{
		$schemaManager = $this->connection->createSchemaManager();
		if ($schemaManager->tablesExist(['migrations'])) {
			return true;
		}
		return false;
	}

}