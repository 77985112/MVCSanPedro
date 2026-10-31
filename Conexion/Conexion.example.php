<?php
class Conexion {
    // Credenciales reales de cada instalacion. Copie este archivo como
    // Conexion.php y complete los valores del hosting; no debe subirse a Git.
    // Puede fijarse el mismo valor por variable de entorno (BD_HOST, BD_USUARIO,
    // BD_CLAVE, BD_NOMBRE) para cambiar de base sin editar este archivo.
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $db_name = "bdsanpedro";
    public $conn;

    public function __construct()
    {
        $env = [
            'host' => getenv('BD_HOST'),
            'username' => getenv('BD_USUARIO'),
            'password' => getenv('BD_CLAVE'),
            'db_name' => getenv('BD_NOMBRE'),
        ];

        foreach ($env as $campo => $valor) {
            if (is_string($valor) && $valor !== '') {
                $this->$campo = $valor;
            }
        }
    }

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
