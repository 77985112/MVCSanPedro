<?php
class Conexion {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $db_name = "bdsanpedro";
    public $conn;

    public function getConnection(){
        $this->conn = null;

        try{
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            if ($this->conn->connect_error) {
                die("Connection failed: " . $this->conn->connect_error);
            }
            $this->conn->set_charset("utf8mb4");
        }catch(Exception $exception){
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }

    public function query($sql){
        return $this->conn->query($sql);
    }
}
?>