<?php
/**
 * PDO Database Wrapper
 * Provides a simplified interface for database operations
 */
class Database {
    private static $instance = null;
    private $pdo;
    private $stmt;

    /**
     * Constructor - connects to the database
     */
    private function __construct() {
        // Check if setup_complete.php exists
        if (!file_exists(__DIR__ . '/../config/setup_complete.php')) {
            header('Location: /Library_System/setup/');
            exit;
        }

        $config = require_once __DIR__ . '/../config/xampp.php';

        try {
            // First try to connect without specifying the database
            $dsn = 'mysql:host=' . $config['db']['host'] . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];

            $tempPdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], $options);

            // Check if database exists
            $stmt = $tempPdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$config['db']['name']}'");
            $dbExists = $stmt->fetch();

            if (!$dbExists) {
                // Database doesn't exist, redirect to setup
                header('Location: /Library_System/setup/');
                exit;
            }

            // Database exists, connect to it
            $dsn = 'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['name'] . ';charset=utf8mb4';
            $this->pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], $options);

        } catch (PDOException $e) {
            // Any connection error should redirect to setup
            header('Location: /Library_System/setup/');
            exit;
        }
    }

    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Prepare statement with query
     */
    public function query($sql) {
        $this->stmt = $this->pdo->prepare($sql);
        return $this;
    }

    /**
     * Bind values to prepared statement
     */
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }

        $this->stmt->bindValue($param, $value, $type);
        return $this;
    }

    /**
     * Execute the prepared statement
     */
    public function execute() {
        return $this->stmt->execute();
    }

    /**
     * Get result set as array of objects
     */
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    /**
     * Get single record as object
     */
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    /**
     * Get row count
     */
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    /**
     * Get last inserted ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollBack() {
        return $this->pdo->rollBack();
    }
}
