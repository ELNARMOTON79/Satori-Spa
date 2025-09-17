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
        protected $conexion;

        public function __construct()
        {
            $this->abrir_conexion();
        }

        public function __destruct()
        {
            $this->cerrar_conexion();
        }

        private function abrir_conexion()
        {
            if (!function_exists('pg_connect')) {
                throw new Exception("La extensión de PostgreSQL no está habilitada en PHP.");
            }

            $conn_string = "host=$this->host port=$this->puerto dbname=$this->base user=$this->usuario password=$this->password";
            // Suprimimos el warning de PHP para manejar el error con una excepción
            $this->conexion = @pg_connect($conn_string);

            if (!$this->conexion) {
                throw new Exception("Error de conexión: " . pg_last_error());
            }
        }

        private function cerrar_conexion()
        {
            if ($this->conexion) {
                pg_close($this->conexion);
                $this->conexion = null;
            }
        }

        /**
         * Ejecuta una consulta que no devuelve filas (INSERT, UPDATE, DELETE).
         * @param string $sql La consulta SQL.
         * @param array $params Los parámetros para la consulta preparada.
         * @return resource|false El resultado de la consulta o false en caso de error.
         */
        public function ejecutar_sentencia($sql, $params = [])
        {
            $result = pg_query_params($this->conexion, $sql, $params);
            if (!$result) {
                throw new Exception("Error al ejecutar la sentencia: " . pg_last_error($this->conexion));
            }
            return $result;
        }

        /**
         * Ejecuta una consulta (SELECT) y devuelve todas las filas como un array asociativo.
         * @param string $sql La consulta SQL.
         * @param array $params Los parámetros para la consulta preparada.
         * @return array Un array con los resultados.
         */
        public function obtener_sentencia($sql, $params = [])
        {
            $result = pg_query_params($this->conexion, $sql, $params);
            if (!$result) {
                throw new Exception("Error al obtener la sentencia: " . pg_last_error($this->conexion));
            }
            return pg_fetch_all($result, PGSQL_ASSOC) ?: [];
        }
    }
?>