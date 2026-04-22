<?php
require 'verificarADM.php'; // Se não for admin, ele é expulso para a dashboard
?>
<!DOCTYPE html>
<html>
<head><title>Administração</title></head>
<body>
    <h1>Área Restrita</h1>
    <p>Apenas o administrador vê isto.</p>
    <a href="home.php">Voltar</a>
</body>
</html>