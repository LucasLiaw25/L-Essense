<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start();
}

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    // O ../ sai da pasta auth e o user/ entra na pasta do login
    header("Location: ../user/login.php");
    exit();
}
?>