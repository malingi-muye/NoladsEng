<?php
class Database {
    private static $instance = null;
    private $pdo;
    private $config;

    private function __construct() {
        $this->config = require __DIR__ . '/../config/config.php';
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $this->config['db']['host'],
            $this->config['db']['name'],
            $this->config['db']['charset']
        );
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO(
                $dsn,
                $this->config['db']['user'],
                $this->config['db']['pass'],
                $options
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection error");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}
