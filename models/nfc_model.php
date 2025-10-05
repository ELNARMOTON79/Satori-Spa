<?php
class NfcModel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    public function generarToken() {
        // Genera un token aleatorio de 32 caracteres (como tu ejemplo)
        return bin2hex(random_bytes(16));
    }

    public function vincularUsuario($id_usuario) {
        $token = $this->generarToken();

        $query = "INSERT INTO nfc (id_usuario, token) VALUES (:id_usuario, :token)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':token', $token);

        return $stmt->execute() ? $token : false;
    }

    public function obtenerUsuarios() {
        $query = "SELECT id, nombre FROM usuarios ORDER BY nombre ASC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
