<?php
session_start();
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']); // Limpa a mensagem após exibi-la
} else {
    $error_message = "";
}
?>