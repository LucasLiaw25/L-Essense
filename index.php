<?php
session_start();
// Se o usuário já estiver logado, manda direto para a dashboard
if(isset($_SESSION['logado']) && $_SESSION['logado'] === true){
    header("Location: dashboard.php");
    exit();
}

include 'usuario_permitidos.php';
$erro = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $nome_digitado  = trim($_POST['nome'] ?? '');
    $email_digitado = trim($_POST['Email'] ?? '');
    $senha_digitada = $_POST['senha'] ?? '';

    if(empty($nome_digitado) || empty($email_digitado) || empty($senha_digitada)){
        $erro = "Por favor, preencha todos os campos!";
    } 
    // 1. Verifica se o nome existe no array
    elseif(isset($usuario_permitidos[$nome_digitado])) {
        $dados_usuario = $usuario_permitidos[$nome_digitado];
        
        // 2. Verifica se o email e a senha batem com os dados desse usuário específico
        if($email_digitado === $dados_usuario['email'] && $senha_digitada === $dados_usuario['senha']) {
            $_SESSION['logado'] = true;
            $_SESSION['usuario'] = $nome_digitado;
            header("Location: dashboard.php");
            exit();
        } else {
            $erro = "E-mail ou senha incorretos para este usuário.";
        }
    } else {
        $erro = "Usuário não encontrado.";
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Login</title>
</head>
<div class="login-container">
        <h1>Login</h1>
        <?php if(!empty($erro)): ?>
            <div class="erro-msg"><?php echo $erro; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <label>Nome:</label>
            <input type="text" name="nome" placeholder="Usuario">
            
            <label>E-mail:</label>
            <input type="email" name="Email" placeholder="exemplo@email.com">
            
            <label>Senha:</label>
            <input type="password" name="senha" placeholder="**********">
            
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>