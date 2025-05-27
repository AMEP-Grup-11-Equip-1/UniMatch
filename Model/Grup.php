<?php
class Grup {
    private $conn;

    public function __construct($connexio) {
        $this->conn = $connexio;
    }

    public function insertarMensajeGrupo($grupo_id, $emisor_id, $mensaje) {
    $sql = "INSERT INTO mensajes_grupo (grupo_id, emisor_id, mensaje) VALUES (?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        error_log("Error preparando consulta en insertarMensajeGrupo: " . $this->conn->error);
        return ["success" => false, "error" => "Error preparando la consulta: " . $this->conn->error];
    }
    $stmt->bind_param("iis", $grupo_id, $emisor_id, $mensaje);
    if ($stmt->execute()) {
        return ["success" => true];
    } else {
        error_log("Error ejecutando consulta en insertarMensajeGrupo: " . $stmt->error);
        return ["success" => false, "error" => "Error ejecutando la consulta: " . $stmt->error];
    }
}


    
   public function crearGrup($nom, $descripcio, $visibilitat, $usuario_id, $urlImagen) {
    $sql = "INSERT INTO grups (nom, descripcio, visibilitat, propietari_id, imagen) VALUES (?, ?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    if ($stmt === false) {
        error_log("Error al preparar la consulta: " . $this->conn->error);
        return false;
    }
    $stmt->bind_param("sssis", $nom, $descripcio, $visibilitat, $usuario_id, $urlImagen);
    if (!$stmt->execute()) {
        error_log("Error en la ejecución de la consulta: " . $stmt->error);
        $stmt->close();
        return false;
    }
    $stmt->close();
    return true;
}

    

    // Afegir un usuari al grup
public function afegirUsuariAlGrup($grup_id, $usuari_id, $rol = "integrant") {
    $sql = "INSERT IGNORE INTO grup_usuaris (grup_id, usuari_id, rol) VALUES (?, ?, ?)";
    $stmt = $this->conn->prepare($sql);

    if (!$stmt) {
        error_log("Error preparando consulta: " . $this->conn->error);
        return false;
    }

    $stmt->bind_param("iis", $grup_id, $usuari_id, $rol);

    $success = $stmt->execute();
    if (!$success) {
        error_log("Error ejecutando consulta: " . $stmt->error);
        return false;
    }

    error_log("Insertado o ignorado: grup_id=$grup_id, usuari_id=$usuari_id, rol=$rol");
    error_log("Filas afectadas: " . $stmt->affected_rows);

    return true;
}




    // Expulsar un usuari del grup
    public function expulsarUsuari($grup_id, $usuari_id) {
        $sql = "DELETE FROM grup_usuaris WHERE grup_id = ? AND usuari_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $grup_id, $usuari_id);
        return $stmt->execute();
    }

    // Assignar rol a un usuari del grup
    public function assignarRol($grup_id, $usuari_id, $nou_rol) {
        $sql = "UPDATE grup_usuaris SET rol = ? WHERE grup_id = ? AND usuari_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $nou_rol, $grup_id, $usuari_id);
        return $stmt->execute();
    }

    // Editar informació del grup
    public function editarGrup($grup_id, $nom, $descripcio, $visibilitat) {
        $sql = "UPDATE grups SET nom = ?, descripcio = ?, visibilitat = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", $nom, $descripcio, $visibilitat, $grup_id);
        return $stmt->execute();
    }

    // Dissoldre un grup
    public function dissoldreGrup($grup_id) {
        $sql = "DELETE FROM grups WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $grup_id);
        return $stmt->execute();
    }

    // Afegir activitat al calendari
    public function afegirActivitat($grup_id, $activitat, $descripcio, $data_event) {
        $sql = "INSERT INTO grup_calendari (grup_id, activitat, descripcio, data_event) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $grup_id, $activitat, $descripcio, $data_event);
        return $stmt->execute();
    }

    // Obtenir membres del grup
    public function obtenirMembres($grup_id) {
        $sql = "SELECT u.id, u.nom_usuari, gu.rol 
                FROM grup_usuaris gu 
                JOIN usuarios u ON gu.usuari_id = u.id 
                WHERE gu.grup_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $grup_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Validar si un usuari és propietari
    public function esPropietari($grup_id, $usuari_id) {
        $sql = "SELECT * FROM grups WHERE id = ? AND propietari_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $grup_id, $usuari_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

 // Obtener todos los grupos con el propietario_id
    public function obtenirTotsElsGrups() {
        $sql = "SELECT id, nom, descripcio, visibilitat, propietari_id FROM grups";  // Incluimos el campo propietari_id
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $grups = [];
        
        while ($row = $result->fetch_assoc()) {
            $grups[] = $row;
        }
        
        return $grups;
    }

    
}
?>
