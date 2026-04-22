<?php
require_once 'VerificarLogin.php'; // Garante que está logado

// Só permite o acesso se o perfil na sessão for 'admin'
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'admin') {
    header("Location: dashboard.php?erro=sem_permissao");
    exit();
}
?>