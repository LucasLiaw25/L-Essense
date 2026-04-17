<?php
require_once 'VerificarADM.php';


function usuarioEhAdmin() {
    // Verifica se o nome do usuário na sessão é 'admin'
    return (isset($_SESSION['usuario']) && $_SESSION['usuario'] === 'admin');
}

if (!usuarioEhAdmin()) {
    // Redireciona para a dashboard com uma mensagem de erro na URL
    header("Location: dashboard.php?erro=sem_permissao");
    exit();
}
?>