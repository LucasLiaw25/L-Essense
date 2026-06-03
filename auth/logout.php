<?php
    session_start();
    session_unset();
    session_destroy();
    setcookie('carrinho_lessence', '', time() - 3600, '/' );
    // O ../ volta para a raiz L-Essense, e o user/ entra na pasta correta
    header("Location: ../user/index.php");
    exit();
?>