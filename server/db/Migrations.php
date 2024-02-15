<?php

/**
 * Class Migrations
 *
 * Represents a class responsible for managing database migrations.
 */
readonly class Migrations
{
    /**
     * @var string The SQL file for finding a specific migration in the migrations' history.
     */
    private const FIND_MIGRATION_SQL = "find_migration.sql";

    /**
     * @var string The SQL file for adding a migration to the migrations' history.
     */
    private const ADD_MIGRATION_SQL = "add_migration.sql";

    /**
     * @var string The SQL file for creating the migrations history table.
     */
    private const CREATE_MIGRATIONS_HISTORY_SQL = "create_migrations_history.sql";

    /**
     * @var Connection The database connection instance.
     */
    private Connection $db;

    /**
     * Migrations constructor.
     *
     * Initializes a new instance of the Migrations class and sets up the database connection.
     */
    public function __construct() {
        $this->db = Connection::getInstance();
    }

    /**
     * Executes the necessary steps to perform migrations.
     */
    public function makeMigrations(): void
    {
        $this->createMigrationsHistory();
        $this->migrations($this->getMigrationsFiles());
    }

    /**
     * Performs the actual migration process.
     *
     * @param array $migrationFiles An array of migration files to be processed.
     */
    private function migrations(array $migrationFiles): void
    {
        foreach ($migrationFiles as $migration) {
            if (!$this->isMigrationCompleted($migration)) {
                $this->completeMigration($migration);
                $this->insertMigration($migration);
            }
        }
    }

    /**
     * Checks if a specific migration has already been completed.
     *
     * @param string $migration The name of the migration to check.
     *
     * @return bool True if the migration is completed, false otherwise.
     */
    private function isMigrationCompleted(string $migration): bool
    {
        return $this->db->query(
            file_get_contents(DbConstants::SQL_PATH . self::FIND_MIGRATION_SQL),
            ['s', [$migration]]
        )->num_rows !== 0;
    }

    /**
     * Marks a migration as completed by executing the corresponding SQL file.
     *
     * @param string $migration The name of the migration to complete.
     */
    private function completeMigration(string $migration): void
    {
        $this->db->query(file_get_contents(DbConstants::MIGRATIONS_PATH . "$migration"));
    }

    /**
     * Inserts a migration into the migrations' history.
     *
     * @param string $migration The name of the migration to insert.
     */
    private function insertMigration(string $migration): void
    {
        $this->db->query(
            file_get_contents(DbConstants::SQL_PATH . self::ADD_MIGRATION_SQL),
            ['s', [$migration]]
        );
    }

    /**
     * Creates the migrations history table if it does not exist.
     */
    private function createMigrationsHistory(): void
    {
        $this->db->query(file_get_contents(DbConstants::SQL_PATH . self::CREATE_MIGRATIONS_HISTORY_SQL));
    }

    /**
     * Gets an array of migration files from the migrations directory.
     *
     * @return array An array containing the names of migration files.
     */
    private function getMigrationsFiles(): array
    {
        return array_diff(scandir(DbConstants::MIGRATIONS_PATH), ['.', '..']);
    }
}
