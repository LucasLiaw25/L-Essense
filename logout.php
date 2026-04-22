<?php
session_start(); // Localiza a sessão atual
session_unset(); // Remove todas as variáveis de sessão
session_destroy(); // Destrói a sessão completamente

// Redireciona para o index.php (sua tela de login)
header("Location: index.php");
exit();
?>