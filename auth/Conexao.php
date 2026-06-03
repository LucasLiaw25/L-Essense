<?php 
// auth/Conexao.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Sao_Paulo');

$servidor = "localhost";
$usuario  = "root";
$senha    = "";
$dbname   = "lessence";

$conexao = mysqli_connect($servidor, $usuario, $senha, $dbname, 3309);

if (!$conexao) {
    die("A conexão com o banco de dados falhou: " . mysqli_connect_error());
}
?>