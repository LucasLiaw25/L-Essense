<?php
// user/menu.php
?>
<?php
// user/menu.php
?>
<nav class="flex flex-col md:flex-row items-center justify-between gap-4 py-4 px-6 mb-10 bg-white border border-stone-200/80 rounded-2xl shadow-sm sticky top-4 z-50 backdrop-blur-md bg-white/90">
    <a href="../user/perfil.php" class="flex items-center gap-3 text-center md:text-left hover:bg-stone-50 p-2 rounded-xl transition-all duration-200 group">
        <div class="h-8 w-8 bg-stone-950 rounded-xl flex items-center justify-center text-white font-serif italic font-bold text-lg group-hover:scale-105 transition-all">L</div>
        <div>
            <span class="font-serif text-xl text-stone-900 leading-none block group-hover:text-stone-700 transition-all">
                <strong><?php echo htmlspecialchars($_SESSION['usuario'] ?? 'Convidado'); ?></strong>
            </span>
            <span class="text-stone-400 text-[10px] font-black uppercase tracking-widest block mt-0.5">
                / <?php echo (isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'admin') ? 'Gestor' : 'Cliente'; ?> 
                <span class="text-stone-300 normal-case font-normal group-hover:text-stone-500 transition-all">(Editar)</span>
            </span>
        </div>
    </a>

    <div class="flex flex-wrap items-center justify-center gap-2 font-sans">
        <a href="../user/home.php" class="flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-wider text-stone-600 hover:text-stone-900 hover:bg-stone-100/80 rounded-xl transition-all duration-200 active:scale-95">
            <i data-lucide=\"home\" class=\"w-3.5 h-3.5\"></i> Início
        </a>

        <a href="../dashboard/status.php" class="flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-wider text-stone-600 hover:text-stone-900 hover:bg-stone-100/80 rounded-xl transition-all duration-200 active:scale-95">
            <i data-lucide=\"clock\" class=\"w-3.5 h-3.5\"></i> Pedidos
        </a>

        <?php if (isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'admin'): ?>
            <a href="../dashboard/product.php" class="flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-wider bg-stone-900 text-stone-50 hover:bg-black hover:shadow-md rounded-xl transition-all duration-200 active:scale-95">
                <i data-lucide=\"box\" class=\"w-3.5 h-3.5\"></i> Painel Produtos
            </a>
            <a href="../dashboard/client.php" class="flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-wider bg-stone-900 text-stone-50 hover:bg-black hover:shadow-md rounded-xl transition-all duration-200 active:scale-95">
                <i data-lucide=\"users\" class=\"w-3.5 h-3.5\"></i> Painel Clientes
            </a>
        <?php endif; ?>
        
        <a href="../auth/logout.php" class="flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-wider bg-red-50 text-red-600 hover:bg-red-100 rounded-xl transition-all duration-200 active:scale-95">
            <i data-lucide=\"log-out\" class=\"w-3.5 h-3.5\"></i> Sair
        </a>
    </div>
</nav>