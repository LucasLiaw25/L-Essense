<?php
// user/menu.php
?>
<nav class="flex flex-col md:flex-row items-center justify-between gap-4 py-4 px-6 mb-10 bg-white border border-stone-200/80 rounded-2xl shadow-sm sticky top-4 z-50 backdrop-blur-md bg-white/90">
    <div class="flex items-center gap-3 text-center md:text-left">
        <div class="h-8 w-8 bg-stone-950 rounded-xl flex items-center justify-center text-white font-serif italic font-bold text-lg">L</div>
        <div>
            <span class="font-serif text-xl text-stone-900 leading-none">
                <strong><?php echo htmlspecialchars($_SESSION['usuario'] ?? 'Convidado'); ?></strong>
            </span>
            <span class="text-stone-400 text-[10px] font-black uppercase tracking-widest block">
                / <?php echo (isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'admin') ? 'Gestor' : 'Cliente'; ?>
            </span>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-center gap-2 font-sans">
        <a href="../user/home.php" class="flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-wider text-stone-600 hover:text-stone-900 hover:bg-stone-100/70 rounded-xl transition-all">
            <i data-lucide="home" class="w-3.5 h-3.5"></i> Início
        </a>

        <a href="../dashboard/status.php" class="flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-wider text-stone-600 hover:text-stone-900 hover:bg-stone-100/70 rounded-xl transition-all">
            <i data-lucide="clock" class="w-3.5 h-3.5"></i> Pedidos
        </a>

        <?php if (isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'admin'): ?>
            <a href="../dashboard/product.php" class="flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-wider bg-stone-900 text-stone-50 hover:bg-black rounded-xl transition-all shadow-sm">
                <i data-lucide="box" class="w-3.5 h-3.5"></i> Painel Produtos
            </a>
            <a href="../dashboard/client.php" class="flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-wider bg-stone-900 text-stone-50 hover:bg-black rounded-xl transition-all shadow-sm">
                <i data-lucide="users" class="w-3.5 h-3.5"></i> Painel Clientes
            </a>
        <?php endif; ?>

        <a href="../auth/logout.php" class="flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-wider text-red-500 hover:bg-red-50 rounded-xl transition-all ml-2">
            <i data-lucide="log-out" class="w-3.5 h-3.5"></i> Sair
        </a>
    </div>
</nav>