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
        protected $conexion = null;

        private function abrir_conexion()
        {
            $conn_string = "host=$this->host port=$this->puerto dbname=$this->base user=$this->usuario password=$this->password";
            $this->conexion = pg_connect($conn_string);

            if (!$this->conexion) {
                die("Error de conexión: " . pg_last_error());
            }
        }

        public function cerrar_conexion() 
        {
            if ($this->conexion && gettype($this->conexion) === 'resource') {
                pg_close($this->conexion);
                $this->conexion = null;
            }
        }


        public function __destruct()
        {
            $this->cerrar_conexion();
        }

        public function getConexion()
        {
            if ($this->conexion === null) {
                $this->abrir_conexion();
            }
            return $this->conexion;
        }

        public function ejecutar_sentencia()
        {
            $conexion = $this->getConexion();
            $bandera = pg_query($conexion, $this->sentencia);
            return $bandera;
        }

        public function obtener_sentencia()
        {
            $conexion = $this->getConexion();
            $result = pg_query($conexion, $this->sentencia);
            return $result;
        }
        
        public function obtener_ultimo_id()
        {
            // Para PostgreSQL, necesitamos una consulta específica
            $result = pg_query($this->getConexion(), "SELECT lastval() as last_id");
            if ($result) {
                $row = pg_fetch_assoc($result);
                return $row['last_id'] ?? null;
            }
            return null;
        }
    }
?>