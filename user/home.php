<?php
require __DIR__ . '/../auth/VerificarLogin.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurante - Painel Principal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Instrument Serif', serif; }
    </style>
</head>
<body class="bg-white text-stone-900 antialiased">

    <div class="max-w-6xl mx-auto px-6">
        <?php require 'menu.php'; ?>

        <?php if (isset($_GET['erro']) && $_GET['erro'] === 'sem_permissao'): ?>
        <div class="mb-8 flex items-center gap-3 p-4 bg-red-50 border border-red-100 text-red-600 rounded-2xl animate-pulse">
            <i data-lucide="shield-alert" class="w-5 h-5"></i>
            <span class="text-sm font-bold uppercase tracking-tight">Acesso Negado: Esta área é exclusiva para administradores.</span>
        </div>
        <?php endif; ?>

        <main class="py-12">
            <header class="mb-16">
                <h1 class="font-serif text-6xl mb-4 italic text-stone-800">Bem-vindo de volta,</h1>
                <p class="text-stone-400 text-lg max-w-md leading-relaxed">
                    Gerencie os pedidos, estoque e configurações do seu restaurante com elegância e precisão.
                </p>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <div class="p-8 border border-stone-100 rounded-[2rem] bg-stone-50/50 hover:bg-white hover:shadow-xl hover:shadow-stone-100 transition-all group">
                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center mb-6 shadow-sm group-hover:scale-110 transition-transform">
                        <i data-lucide="user" class="w-6 h-6 text-stone-800"></i>
                    </div>
                    <h3 class="font-black uppercase tracking-widest text-xs text-stone-400 mb-2">Seu Perfil</h3>
                    <p class="font-serif text-2xl text-stone-800 mb-4"><?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
                    <span class="inline-block px-3 py-1 bg-stone-200 text-[10px] font-black uppercase tracking-tighter rounded-full">
                        Status: <?php echo $_SESSION['perfil']; ?>
                    </span>
                </div>

                <?php if (isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'admin'): ?>
                <a href="../dashboard/product.php" class="p-8 border border-stone-900 bg-stone-900 text-white rounded-[2rem] hover:bg-black hover:scale-[1.02] transition-all shadow-2xl shadow-stone-200 block">
                    <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center mb-6">
                        <i data-lucide="package" class="w-6 h-6 text-white"></i>
                    </div>
                    <h3 class="font-black uppercase tracking-widest text-[10px] text-stone-400 mb-2">Administração</h3>
                    <p class="font-serif text-2xl mb-4 italic">Gerenciar Catálogo</p>
                    <div class="flex items-center gap-2 text-xs font-bold opacity-70">
                        Acessar inventário <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </div>
                </a>
                <?php endif; ?>

                <div class="p-8 border border-stone-100 rounded-[2rem] bg-white hover:shadow-xl hover:shadow-stone-100 transition-all border-dashed">
                    <div class="w-12 h-12 bg-stone-50 rounded-2xl flex items-center justify-center mb-6 text-stone-400">
                        <i data-lucide="help-circle" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-black uppercase tracking-widest text-xs text-stone-400 mb-2">Suporte</h3>
                    <p class="text-stone-500 text-sm leading-relaxed italic font-serif text-lg">Precisa de ajuda com o sistema? Entre em contato com a equipe técnica.</p>
                </div>

            </div>
        </main>
    </div>
    <?php include 'rodape.php' ?>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>