<?php
namespace App\Core;

class DBConnection {
    protected \mysqli $connection;
    public function __construct(string $host, string $username, string $password, string $database) {
        $this->connection = new \mysqli($host, $username, $password, $database);

        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        } 
    }

    public function getConnection(): \mysqli {
        return $this->connection;
    }

    public function closeConnection(): void {
        $this->connection->close();
    }
}

?>