<?php 
    date_default_timezone_set('America/Sao_Paulo');

    $servidor = "localhost";
    $usuario = "root";
    $senha = "";
    $dbname = "lessence";

    $conexao = mysqli_connect($servidor, $usuario, $senha, $dbname);
    if($conexao->connect_error){
        die("Conexão falhou: " . $conexao->connect_error);
    }
?>