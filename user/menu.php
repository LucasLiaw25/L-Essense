<?php
// Apenas lógica de segurança, sem tags HTML globais aqui
?>
<nav class="flex items-center gap-4 py-6 mb-8 border-b border-stone-100 bg-white/50 backdrop-blur-sm sticky top-0 z-50">
    <div class="flex-1">
        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400 block mb-0.5">Acesso Autorizado</span>
        <span class="font-serif text-2xl text-stone-900 leading-none" style="font-family: 'Instrument Serif', serif;">
            <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong> 
            <span class="text-stone-300 text-sm italic ml-1 font-sans">
                / <?php echo $_SESSION['perfil'] === 'admin' ? 'Gestor' : 'Cliente'; ?>
            </span>
        </span>
    </div>

    <div class="flex items-center gap-2 font-sans">
        <a href="../user/home.php" class="flex items-center gap-2 px-5 py-2.5 text-xs font-black uppercase tracking-widest text-stone-500 hover:text-stone-900 hover:bg-stone-50 rounded-2xl transition-all">
            <i data-lucide="home" class="w-4 h-4"></i> Início
        </a>

        <?php if (isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'admin'): ?>
            <a href="../dashboard/product.php" class="flex items-center gap-2 px-5 py-2.5 text-xs font-black uppercase tracking-widest bg-stone-900 text-stone-50 hover:bg-black rounded-2xl transition-all shadow-md">
                <i data-lucide="package" class="w-4 h-4"></i> Produtos
            </a>
        <?php endif; ?>

        <?php if (isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'admin'): ?>
            <a href="../dashboard/client.php" class="flex items-center gap-2 px-5 py-2.5 text-xs font-black uppercase tracking-widest bg-stone-900 text-stone-50 hover:bg-black rounded-2xl transition-all shadow-md">
                <i data-lucide="package" class="w-4 h-4"></i> Clientes
            </a>
        <?php endif; ?>
        <a href="../auth/logout.php" class="flex items-center gap-2 px-5 py-2.5 text-xs font-black uppercase tracking-widest text-red-400 hover:text-red-600 hover:bg-red-50 rounded-2xl transition-all">
            <i data-lucide="log-out" class="w-4 h-4"></i> Sair
        </a>
    </div>
</nav>

<script>
    // Força a criação dos ícones assim que o menu carrega
    if (typeof lucide !== 'undefined') { lucide.createIcons(); }
</script>