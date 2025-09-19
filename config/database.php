<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    class Conexion
    {
        private $host = 'dpg-d309i7d6ubrc73elvu10-a.oregon-postgres.render.com';
        private $usuario = 'root';
        private $password = '7PnXDiakUbL3hx7DEG0tQevrvAvmlNYK';
        private $base = 'satori';
        private $puerto = '5432';
        public $sentencia;
        private $conexion;

        private function abrir_conexion()
        {
            $conn_string = "host=$this->host port=$this->puerto dbname=$this->base user=$this->usuario password=$this->password";
            $this->conexion = pg_connect($conn_string);

            if (!$this->conexion) {
                die("Error de conexión: " . pg_last_error());
            }
        }

        private function cerrar_conexion()
        {
            pg_close($this->conexion); 
        }

        public function ejecutar_sentencia()
        {
            $this->abrir_conexion();
            $bandera = pg_query($this->conexion, $this->sentencia);
            $this->cerrar_conexion();
            return $bandera;
        }

        public function obtener_sentencia()
        {
            $this->abrir_conexion();
            $result = pg_query($this->conexion, $this->sentencia);
            return $result;
        }
        
        public function obtener_ultimo_id()
        {
            // Para PostgreSQL, necesitamos una consulta específica
            $this->abrir_conexion();
            $result = pg_query($this->conexion, "SELECT lastval() as last_id");
            if ($result) {
                $row = pg_fetch_assoc($result);
                $last_id = $row['last_id'];
                $this->cerrar_conexion();
                return $last_id;
            }
            $this->cerrar_conexion();
            return null;
        }
    }
?>