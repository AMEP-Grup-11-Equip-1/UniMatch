<?php
class Historia {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function obtenerPorId($id) {
        $sql = "SELECT id, nombre, universidad, descripcion, imagen FROM historias WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            } else {
                return ["error" => "Perfil no encontrado"];
            }
        } else {
            return ["error" => "Error en la consulta: " . $this->conn->error];
        }
    }

    public function obtenerMultiples($limit = 5) {
        $sql = "SELECT id, nombre, universidad, descripcion, imagen FROM historias ORDER BY id ASC LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $perfiles = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $perfiles[] = $row;
            }
            return $perfiles;
        } else {
            return ["error" => "No se encontraron perfiles"];
        }
    }
    public function crear($nombre, $universidad, $descripcion, $urlImagen) {
        $sql = "INSERT INTO historias (nombre, universidad, descripcion, imagen) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $nombre, $universidad, $descripcion, $urlImagen);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
?>
