<?php
// Se ambos estão em auth/, remova o ../
require_once __DIR__ . '/VerificarLogin.php'; 

if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'admin') {
    // Para voltar para a home que está em user/
    header("Location: ../user/home.php?erro=sem_permissao");
    exit();
}
?>