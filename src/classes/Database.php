<?php

namespace TennisApp;

class Database extends \PDO
{
    public function __construct(string $dsn, string $username, string $password, array $options = [])
    {
        $default_options[\PDO::ATTR_DEFAULT_FETCH_MODE] = \PDO::FETCH_ASSOC; // Return data as array
        $default_options[\PDO::ATTR_EMULATE_PREPARES]   = false;            // Emulate prepares off
        $default_options[\PDO::ATTR_ERRMODE]            = \PDO::ERRMODE_EXCEPTION; // Error settings
        $options = array_replace($default_options, $options);      // Replace defaults if supplied
        parent::__construct($dsn, $username, $password, $options); // Create PDO object
    }

    /**
     * Executes an SQL query with optional arguments.
     *
     * @param string $sql The SQL query to execute.
     * @param array|null $arguments Optional arguments for the prepared statement.
     * @return \PDOStatement The resulting PDOStatement object.
     */
    public function runSQL(string $sql, ?array $arguments = null)
    {
        if ($arguments === null) { // If no arguments
            return $this->query($sql); // Run SQL, return PDOStatement object
        }
        try {
            $statement = $this->prepare($sql);        // If still running prepare statement
            $statement->execute($arguments); // Execute SQL statement with arguments
            return $statement; // Return PDOStatement object
        } catch (\PDOException $e) {
            throw new \RuntimeException("Database query error: " . $e->getMessage(), 0, $e);
        }
    }
}
