<?php
// O menu.php já é chamado dentro de um ambiente com sessão iniciada
if (isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'admin'): ?>
    <a href="painel_admin.php" class="btn-admin" style="margin-right: 15px; background-color: #1a73e8; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none;">Painel Administrativo</a>
<?php endif; ?>

<a href="logout.php" class="btn-sair">Sair</a>