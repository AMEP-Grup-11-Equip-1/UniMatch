<?php
require_once 'models/Grup.php';

class GrupController {
    private $conn;
    private $grupModel;

    public function __construct($connexio) {
        $this->conn = $connexio;
        $this->grupModel = new Grup($this->conn);
    }

    // Crear grup
    public function crear() {
        $nom = $_POST['nom'];
        $descripcio = $_POST['descripcio'] ?? "";
        $visibilitat = $_POST['visibilitat'] ?? 'privat';
        $usuari_id = $_SESSION['usuarioID'];

        $resultat = $this->grupModel->crearGrup($nom, $descripcio, $visibilitat, $usuari_id);
        return $resultat;
    }

    // Afegir usuari a un grup
    public function afegirUsuari() {
        $grup_id = $_POST['grup_id'];
        $usuari_nom = $_POST['nom_usuari']; // Manolito
        $usuari_id = $this->obtenirIdUsuariPerNom($usuari_nom);

        if (!$usuari_id) {
            return ["status" => "error", "message" => "Usuari no trobat"];
        }

        return $this->grupModel->afegirUsuariAlGrup($grup_id, $usuari_id);
    }

    // Expulsar usuari
    public function expulsarUsuari() {
        $grup_id = $_POST['grup_id'];
        $usuari_id = $_POST['usuari_id'];
        return $this->grupModel->expulsarUsuari($grup_id, $usuari_id);
    }

    // Assignar rol
    public function assignarRol() {
        $grup_id = $_POST['grup_id'];
        $usuari_id = $_POST['usuari_id'];
        $nou_rol = $_POST['rol'];

        return $this->grupModel->assignarRol($grup_id, $usuari_id, $nou_rol);
    }

    // Editar grup
    public function editar() {
        $grup_id = $_POST['grup_id'];
        $nom = $_POST['nom'];
        $descripcio = $_POST['descripcio'];
        $visibilitat = $_POST['visibilitat'];

        return $this->grupModel->editarGrup($grup_id, $nom, $descripcio, $visibilitat);
    }

    // Dissoldre grup
    public function dissoldre() {
        $grup_id = $_POST['grup_id'];
        return $this->grupModel->dissoldreGrup($grup_id);
    }

    // Afegir activitat al calendari
    public function afegirActivitat() {
        $grup_id = $_POST['grup_id'];
        $activitat = $_POST['activitat'];
        $descripcio = $_POST['descripcio'];
        $data_event = $_POST['data_event'];

        return $this->grupModel->afegirActivitat($grup_id, $activitat, $descripcio, $data_event);
    }

    // Obtenir membres
    public function obtenirMembres() {
        $grup_id = $_GET['grup_id'];
        return $this->grupModel->obtenirMembres($grup_id);
    }

    // Obtenir ID d’un usuari pel seu nom (ajuda per afegir)
    private function obtenirIdUsuariPerNom($nom_usuari) {
        $sql = "SELECT id FROM usuarios WHERE nom_usuari = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $nom_usuari);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($fila = $result->fetch_assoc()) {
            return $fila['id'];
        }
        return null;
    }
}

// Crear grup	    grupController.php?action=crear	                    POST	Inclou nom, descripció...
// Afegir usuari	grupController.php?action=afegirUsuari	            POST	Nom d’usuari i ID grup
// Expulsar usuari	grupController.php?action=expulsarUsuari            POST	ID d’usuari i grup
// Assignar rol	    grupController.php?action=assignarRol	            POST	Només propietari
// Editar grup	    grupController.php?action=editar	                POST	Nom, visibilitat, etc.
// Dissoldre grup	grupController.php?action=dissoldre	                POST	Només propietari
// Afegir activitat	grupController.php?action=afegirActivitat	        POST	Títol, data
// Llistar membres	grupController.php?action=obtenirMembres&grup_id=1	GET	
?>
