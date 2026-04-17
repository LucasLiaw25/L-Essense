<footer class="footer-sistema">
    <p>
        Status: 
        <?php if (isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'admin'): ?>
            <span style="color: #1a73e8; font-weight: bold;">[ ADMINISTRADOR ]</span>
        <?php else: ?>
            <span style="color: #666;">[ USUÁRIO NORMAL ]</span>
        <?php endif; ?>
    </p>
    <small>© <?php echo date("Y"); ?> - Sistema do Restaurante</small>
</footer>