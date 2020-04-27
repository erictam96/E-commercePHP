<?php
class connect_db {
    private $conn;
    private $host = "localhost";
    private $user = "id6667176_admin";
    private $password = "0173873588";
    private $db = "id6667176_ecommercefyp";

    // Connecting to database
    public function connect() {

        // Connecting to mysql database
        $this->conn = new mysqli($this->host, $this->user, $this->password, $this->db);
        // return database object
        return $this->conn;
    }
}

?>