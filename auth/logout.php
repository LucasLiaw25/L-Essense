<?php
    session_start();
    session_unset();
    session_destroy();
    // O ../ volta para a raiz L-Essense, e o user/ entra na pasta correta
    header("Location: ../user/login.php");
    exit();
?>