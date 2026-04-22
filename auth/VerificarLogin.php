<?php
if (session_status() === PHP_SESSION_NONE) { //verificar se a sessão ja foi iniciada
    session_start();
}

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: user/login.php");
    exit();
}