<?php
class Grup {
    private $conn;

    public function __construct($connexio) {
        $this->conn = $connexio;
    }

    // Crear un nou grup
    public function crearGrup($nom, $descripcio, $visibilitat, $propietari_id, $enllac_invitacio = null) {
        $sql = "INSERT INTO grups (nom, descripcio, visibilitat, propietari_id, enllac_invitacio) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssiss", $nom, $descripcio, $visibilitat, $propietari_id, $enllac_invitacio);
        
        if ($stmt->execute()) {
            $grup_id = $this->conn->insert_id;

            // Afegir el propietari al grup amb rol 'propietari'
            $this->afegirUsuariAlGrup($grup_id, $propietari_id, "propietari");

            return ["status" => "success", "grup_id" => $grup_id];
        }
        return ["status" => "error", "message" => "Error creant el grup"];
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
// Funcionalidades:
// crearGrup	Crea un grup i afegeix el propietari amb rol
// afegirUsuariAlGrup	Afegeix un usuari com a integrant o propietari
// expulsarUsuari	Elimina un usuari del grup
// assignarRol	Canvia el rol d’un membre del grup
// editarGrup	Permet modificar nom, descripció i visibilitat
// dissoldreGrup	Esborra el grup completament
// afegirActivitat	Afegeix una activitat al calendari del grup
// obtenirMembres	Retorna els membres del grup amb els seus rols
// esPropietari	Comprova si l’usuari és el propietari del grup 
?>

