<?php
// Inicia o reanuda la sesión existente
session_start();

// Establece el tipo de contenido como JSON
header('Content-Type: application/json');

// Verifica si existe una sesión de administrador activa
if (isset($_SESSION['admin'])) {
    $admin = $_SESSION['admin']; // Obtiene los datos del admin de la sesión
    
    // Devuelve los datos clave del admin en formato JSON
    echo json_encode([
        'id' => $admin['id'],     // ID del administrador
        'name' => $admin['name'], // Nombre del admin
        'mail' => $admin['mail']  // Email del admin
    ]);
} else {
    // Devuelve error si no hay sesión activa
    echo json_encode(['error' => 'Usuario no autenticado']);
}
?>