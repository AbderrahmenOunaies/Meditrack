<?php
class Database {
    private static ?Database $instance = null;

    private string $host = "localhost";
    private string $db_name = "meditrack";
    private string $username = "root";
    private string $password = "";

    private ?PDO $connection = null;

    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->connection = new PDO($dsn, $this->username, $this->password, $options);
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->connection;
    }

    public function connect(): PDO {
        // Compatibilité avec l'ancien code
        return $this->getConnection();
    }
}
?>