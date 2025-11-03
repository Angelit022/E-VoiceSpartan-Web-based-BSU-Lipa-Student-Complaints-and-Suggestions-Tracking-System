<?php
class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "e_voice_spartan";
    private $connection;

    public function getConnection() {
        if ($this->connection === null) {
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);
            if ($this->connection->connect_error) {
                die("Error: Failed to connect to MySQL - " . $this->connection->connect_error);
            }
        }
        return $this->connection;
    }

    public function closeConnection() {
        if ($this->connection !== null) {
            $this->connection->close();
            $this->connection = null;
        }
    }
}
?>
