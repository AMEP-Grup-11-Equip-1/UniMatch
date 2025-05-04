<?php
class Usuario {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function eliminar($id) {
        $query = "DELETE FROM usuario WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return ["status" => "error", "message" => "Error en la preparación de la consulta"];
        }

        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Cuenta eliminada correctamente"];
        } else {
            return ["status" => "error", "message" => "Error al ejecutar la consulta"];
        }
    }
    public function actualizarPerfil($id, $nombre_usuario, $email, $password) {
        if ($this->usuarioOCorreoExistente($nombre_usuario, $email, $id)) {
            return ["status" => "error", "message" => "¡El usuario o correo ya están registrados!"];
        }

        $sql = "UPDATE usuario SET nom_usuari = ?, correu_electronic = ?, password = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", $nombre_usuario, $email, $password, $id);

        if ($stmt->execute()) {
            return ["status" => "success"];
        } else {
            return ["status" => "error", "message" => "Error al actualizar los datos del usuario."];
        }
    }

    private function usuarioOCorreoExistente($nombre_usuario, $email, $usuarioID) {
        $sql = "SELECT * FROM usuario WHERE (nom_usuari = ? OR correu_electronic = ?) AND id != ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $nombre_usuario, $email, $usuarioID);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $existe = $resultado->num_rows > 0;
        $stmt->close();

        return $existe;
    }
    public function autenticar($email, $password) {
        $sql = "SELECT id, name, password, mail FROM usuario WHERE mail = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();

            // Aquí podries fer servir password_verify() si hashas el password!
            if ($password === $fila['password']) {
                // Si és correcte retorna l’usuari
                return [
                    "status" => "success",
                    "usuario" => [
                        "id" => $fila['id'],
                        "name" => $fila['name'],
                        "mail" => $fila['mail']
                    ]
                ];
            } else {
                return ["status" => "error", "message" => "¡Contraseña incorrecta!"];
            }
        } else {
            return ["status" => "error", "message" => "¡Usuario no encontrado!"];
        }
    }
}
?>
