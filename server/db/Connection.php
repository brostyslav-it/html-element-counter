<?php

/**
 * Class Connection
 *
 * Represents a singleton database connection using MySQLi.
 */
class Connection
{
    /**
     * @var Connection|null The instance of the Connection class (singleton pattern).
     */
    private static ?Connection $instance = null;

    /**
     * @var mysqli The MySQLi database connection object.
     */
    private mysqli $connection;

    /**
     * @var string The host of the database server.
     */
    private string $host = 'localhost:3306';

    /**
     * @var string The username for connecting to the database.
     */
    private string $username = 'root';

    /**
     * @var string The password for connecting to the database.
     */
    private string $password = 'Rostik2005$';

    /**
     * Connection constructor.
     *
     * Establishes a database connection and creates the database if it does not exist.
     */
    private function __construct()
    {
        $this->connection = new mysqli($this->host, $this->username, $this->password);

        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }

        $this->createDB();
    }

    /**
     * Creates the database by executing the SQL queries from the setup file.
     */
    private function createDB(): void
    {
        $this->connection->multi_query(file_get_contents(DbConstants::DB_SETUP_PATH . 'create_db.sql'));
    }

    /**
     * Gets the instance of the Connection class (singleton pattern).
     *
     * @return Connection The instance of the Connection class.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();

            $migrations = new Migrations();
            $migrations->makeMigrations();
        }

        return self::$instance;
    }

    /**
     * Executes a prepared SQL query with optional parameters and returns the result.
     *
     * @param string $sql The SQL query string.
     * @param array|null $params An array containing the parameter types and values for binding.
     *
     * @return false|mysqli_result The result of the executed query.
     */
    public function query(string $sql, array $params = null): false|mysqli_result
    {
        $this->resetResults();

        $query = $this->connection->prepare($sql);

        if ($params !== null) {
            $query->bind_param($params[0], ...$params[1]);
        }

        $query->execute();

        return $query->get_result();
    }

    /**
     * Returns the auto-generated ID used in the last query.
     *
     * @return int|string The last inserted ID.
     */
    public function id(): int|string
    {
        return $this->connection->insert_id;
    }

    /**
     * Resets the results of any remaining queries.
     */
    private function resetResults(): void
    {
        while ($this->connection->next_result()) {
            $this->connection->store_result();
        }
    }

    /**
     * Prevents cloning of the singleton instance.
     */
    public function __clone() {}

    /**
     * Prevents deserialization of the singleton instance.
     */
    public function __wakeup() {}
}
