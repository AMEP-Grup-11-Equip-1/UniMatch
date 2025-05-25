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

    public function actualizarPerfilCompleto($usuario_id, $nombre, $email, $descripcion, $password, $urlImagen) {
    // No hasheamos la contraseña, la guardamos tal cual si no está vacía
    $passwordToSave = null;
    if (!empty($password)) {
        $passwordToSave = $password;  // Guardar sin hash (texto plano)
    }

    // Construimos la consulta base
    $sql = "UPDATE usuario SET name = ?, mail = ?, descripcion = ?";

    // Parámetros y tipos para bind_param
    $params = [];
    $types = "sss";

    $params[] = &$nombre;
    $params[] = &$email;
    $params[] = &$descripcion;

    // Añadimos password si se ha recibido
    if ($passwordToSave !== null) {
        $sql .= ", password = ?";
        $types .= "s";
        $params[] = &$passwordToSave;
    }

    // Añadimos imagen si se ha recibido y no está vacía
    if (!empty($urlImagen)) {
        $sql .= ", imagen = ?";
        $types .= "s";
        $params[] = &$urlImagen;
    }

    // Condición WHERE
    $sql .= " WHERE id = ?";
    $types .= "i";
    $params[] = &$usuario_id;

    $stmt = $this->conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error al preparar la consulta: " . $this->conn->error);
        return ['status' => 'error', 'message' => 'Error al preparar la consulta'];
    }

    // Llamada dinámica a bind_param
    array_unshift($params, $types);
    call_user_func_array([$stmt, 'bind_param'], $params);

    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Perfil actualizado correctamente'];
    } else {
        error_log("Error en la ejecución de la consulta: " . $stmt->error);
        return ['status' => 'error', 'message' => 'Error al actualizar el perfil'];
    }
}



    public function actualizarPerfilSinUniversidad($usuario_id, $nombre, $email, $descripcion, $urlImagen) {
        $sql = "UPDATE usuario SET name = ?, mail = ?, descripcion = ?, imagen = ? WHERE id = ?";
    
        $stmt = $this->conn->prepare($sql);
    
        if ($stmt === false) {
            error_log("Error al preparar la consulta: " . $this->conn->error);
            return ['status' => 'error', 'message' => 'Error al preparar la consulta'];
        }
    
        $stmt->bind_param("ssssi", $nombre, $email, $descripcion, $urlImagen, $usuario_id);
    
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Perfil actualizado correctamente'];
        } else {
            error_log("Error en la ejecución de la consulta: " . $stmt->error);
            return ['status' => 'error', 'message' => 'Error al actualizar el perfil'];
        }
    }

    private function usuarioOCorreoExistente($name, $email, $usuarioID) {
        $sql = "SELECT * FROM usuario WHERE (name = ? OR mail = ?) AND id != ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $email, $usuarioID);
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



    public function obtenerUsuarioPorID($id) {
        // Consulta SQL para obtener la información del usuario por su ID
        $sql = "SELECT id, name, mail, descripcion, imagen FROM usuario WHERE id = ?";
        
        // Preparamos la consulta
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            // Si ocurre un error al preparar la consulta, devolvemos un error
            return ["status" => "error", "message" => "Error al preparar la consulta SQL"];
        }

        // Enlazamos el parámetro ID en la consulta
        $stmt->bind_param("i", $id);
        
        // Ejecutamos la consulta
        $stmt->execute();
        
        // Obtenemos el resultado de la consulta
        $resultado = $stmt->get_result();

        // Verificamos si el usuario fue encontrado
        if ($resultado->num_rows > 0) {
            // Si se encuentra un usuario, devolvemos los datos
            $usuario = $resultado->fetch_assoc();
            return ["status" => "success", "usuario" => $usuario];
        } else {
            // Si no se encuentra el usuario, devolvemos un error
            return ["status" => "error", "message" => "Usuario no encontrado"];
        }
    }
}

?>
