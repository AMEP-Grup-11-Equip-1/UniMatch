<?php
class Grup {
    private $conn;

    public function __construct($connexio) {
        $this->conn = $connexio;
    }


    
    public function crearGrup($nom, $descripcio, $visibilitat, $usuario_id) {
        // Preparamos la consulta SQL sin el campo enllac_invitacio
        $sql = "INSERT INTO grups (nom, descripcio, visibilitat, propietari_id) VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        // Verificar si la preparación fue exitosa
        if ($stmt === false) {
            error_log("Error al preparar la consulta: " . $this->conn->error);
            return false;
        }

        // Vincular los parámetros
        $stmt->bind_param("sssi", $nom, $descripcio, $visibilitat, $usuario_id);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            return true;
        } else {
            // Mostrar error si la ejecución falla
            error_log("Error en la ejecución de la consulta: " . $stmt->error);
            return false;
        }
    }
    

    // Afegir un usuari al grup
    public function afegirUsuariAlGrup($grup_id, $usuari_id, $rol = "integrant") {
        $sql = "INSERT IGNORE INTO grup_usuaris (grup_id, usuari_id, rol) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iis", $grup_id, $usuari_id, $rol);
        return $stmt->execute();
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
}
?>
