<?php
class Database {
    private $host = 'db';
    private $dbname = 'library_db';
    private $username = 'root';
    private $password = 'rahul';
    private $pdo;

    public function __construct() {
        try {
            // First try to connect to the database
            $this->pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            // If connection fails, try to create the database
            if ($e->getCode() == 1049) { // MySQL error code for "Unknown database"
                try {
                    // Connect without database name
                    $tempPdo = new PDO(
                        "mysql:host={$this->host}",
                        $this->username,
                        $this->password,
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );

                    // Create database if it doesn't exist
                    $tempPdo->exec("CREATE DATABASE IF NOT EXISTS {$this->dbname}");
                    
                    // Connect to the newly created database
                    $this->pdo = new PDO(
                        "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                        $this->username,
                        $this->password,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES => false
                        ]
                    );

                    // Import database schema
                    $this->importSchema();
                } catch (PDOException $e) {
                    throw new Exception("Database creation failed: " . $e->getMessage());
                }
            } else {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
    }

    public function getConnection() {
        return $this->pdo;
    }

    private function importSchema() {
        try {
            // Read and execute SQL file
            $sql = file_get_contents(__DIR__ . '/../database.sql');
            
            // Split SQL file into individual statements
            $statements = array_filter(
                array_map(
                    'trim',
                    explode(';', $sql)
                )
            );

            // Execute each statement
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $this->pdo->exec($statement);
                }
            }
        } catch (PDOException $e) {
            throw new Exception("Schema import failed: " . $e->getMessage());
        }
    }
}
?>