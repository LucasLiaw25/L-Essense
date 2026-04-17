<?php
require 'VerificarLogin.php'; //verificar que só logados entrem
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagina Principal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-body"> <nav>
        <span>Olá, <strong><?php echo $_SESSION['usuario']; ?></strong></span>
        <?php require 'menu.php'; ?>
    </nav>

    <?php if (isset($_GET['erro']) && $_GET['erro'] === 'sem_permissao'): ?>
    <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px;">
        <strong>Acesso Negado!</strong> Você não tem permissão de administrador para acessar aquela página.
    </div>
<?php endif; ?>

    <div class="login-container" style="max-width: 100%;">
        <h2>Bem-vindo ao sistema!</h2>
        <p>Este é o seu painel principal.</p>
    </div>
</body>
</html>