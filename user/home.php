<?php
//Defini a classe primeiro para o PHP saber como ler os objetos da sessão
if (!class_exists('Product')) {
    class Product {
        public int $id;
        public function __construct(
            public string $name,
            public string $description,
            public float $price,
            public int $storage
        ) {}
    }
}
require __DIR__ . '/../auth/VerificarLogin.php';
// Garante que a variável existe para não dar erro de "undefined"
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}
// LOGICA PARA LIMPAR O CARRINHO
if (isset($_GET['limpar_carrinho'])) {
    $_SESSION['carrinho'] = []; // Esvazia o array
    header("Location: home.php"); // Recarrega a página para sumir o menu e limpar a URL
    exit();
}

$produtos = $_SESSION['listProducts']??[];
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurante - Painel Principal</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;700;900&display=swap" rel="stylesheet">

    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .font-serif {
            font-family: 'Instrument Serif', serif;
        }

        /* Animação de transição das imagens do banner */
        @keyframes bannerFade {

            0%,
            33% {
                opacity: 0.4;
            }

            /* Aparece */
            40%,
            100% {
                opacity: 0;
            }

            /* Desaparece */
        }

        .animate-fade-1 {
            animation: bannerFade 15s infinite;
        }

        .animate-fade-2 {
            animation: bannerFade 15s infinite 5s;
        }

        /* Começa com 5s de atraso */
        .animate-fade-3 {
            animation: bannerFade 15s infinite 10s;
        }

        /* Começa com 10s de atraso */
    </style>
</head>

<body class="bg-white text-stone-900 antialiased">
    <div class="max-w-6xl mx-auto px-6">
        <?php require 'menu.php'; ?>
        <?php if (isset($_GET['erro']) && $_GET['erro'] === 'sem_estoque'): ?>
    <div class="fixed top-24 left-1/2 -translate-x-1/2 z-[100] bg-red-600 text-white px-6 py-3 rounded-2xl shadow-2xl font-bold text-xs uppercase tracking-widest animate-bounce">
        Limite de estoque atingido para este item!
    </div>
<?php endif; ?>

        <?php if (isset($_GET['erro']) && $_GET['erro'] === 'sem_permissao'): ?>
            <div class="mb-8 flex items-center gap-3 p-4 bg-red-50 border border-red-100 text-red-600 rounded-2xl animate-pulse">
                <i data-lucide="shield-alert" class="w-5 h-5"></i>
                <span class="text-sm font-bold uppercase tracking-tight">Acesso Negado: Esta área é exclusiva para administradores.</span>
            </div>
        <?php endif; ?>

        <main class="py-12">
            <header class="relative mb-16 rounded-[3rem] overflow-hidden bg-stone-900 min-h-[400px] flex items-center px-12">
                <div class="absolute inset-0 z-0">
                    <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1200&q=80"
                        class="absolute inset-0 w-full h-full object-cover opacity-40 animate-fade-1" alt="Comida 1">
                    <img src="https://images.unsplash.com/photo-1473093226795-af9932fe5856?auto=format&fit=crop&w=1200&q=80"
                        class="absolute inset-0 w-full h-full object-cover opacity-0 animate-fade-2" alt="Comida 2">
                    <img src="https://images.unsplash.com/photo-1543353071-873f17a7a088?auto=format&fit=crop&w=1200&q=80"
                        class="absolute inset-0 w-full h-full object-cover opacity-0 animate-fade-3" alt="Comida 3">
                    <div class="absolute inset-0 bg-gradient-to-r from-black/80 to-transparent"></div>
                </div>
                <div class="relative z-10 max-w-xl">
                    <h1 class="font-serif text-6xl mb-4 italic text-white leading-tight">Bem-vindo de volta,</h1>
                    <p class="text-stone-300 text-lg leading-relaxed font-light">
                        Veja a varieade de refeições bem aqui!.
                    </p>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

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

                    <a href="../dashboard/client.php" class="p-8 border border-stone-900 bg-stone-900 text-white rounded-[2rem] hover:bg-black hover:scale-[1.02] transition-all shadow-2xl shadow-stone-200 block">
                        <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center mb-6">
                            <i data-lucide="user" class="w-6 h-6 text-white"></i>
                        </div>
                        <h3 class="font-black uppercase tracking-widest text-[10px] text-stone-400 mb-2">Administração</h3>
                        <p class="font-serif text-2xl mb-4 italic">Gerenciar Clientes</p>
                        <div class="flex items-center gap-2 text-xs font-bold opacity-70">
                            Acessar Pagina<i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </div>
                    </a>

                     <a href="../dashboard/status.php" class="p-8 border border-stone-900 bg-stone-900 text-white rounded-[2rem] hover:bg-black hover:scale-[1.02] transition-all shadow-2xl shadow-stone-200 block">
                        <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center mb-6">
                            <i data-lucide="user" class="w-6 h-6 text-white"></i>
                        </div>
                        <h3 class="font-black uppercase tracking-widest text-[10px] text-stone-400 mb-2">Administração</h3>
                        <p class="font-serif text-2xl mb-4 italic">Gerenciar Status</p>
                        <div class="flex items-center gap-2 text-xs font-bold opacity-70">
                            Acessar Pedidos<i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </div>
                    </a>
                <?php endif; ?>
            </div>
        </main>
    <main class="py-12">
        <div class="mb-10 px-6">
            <h2 class="font-serif text-4xl italic text-stone-800">Nosso Cardápio</h2>
            <p class="text-stone-400 text-sm traking-widest uppercase font-bold mt-2">Escolha sua experiência </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 px-6">
        <?php if (empty($produtos)): ?>
            <div class="col-span-full py-20 text-center border-2 border-dashed border-stone-100 rounded-[3rem]">
                <i data-lucide="utensils-crosses" class="w-10 h-10 text-stone-200 mx-auto mb-4"></i>
                <p class="font-serif text-xl italic text-stone-400">O cardápio está sendo preparado...</p>
            </div>
    </div>
        <?php else: ?>
            <?php foreach ($produtos as $produto): ?>
    <?php 
        // Verifica se o estoque é zero ou menor
        $estaVazio = ($produto->storage <= 0); 
    ?>
    
    <div class="bg-white border border-stone-200 rounded-[2.5rem] p-8 shadow-sm hover:shadow-xl transition-all group <?php echo $estaVazio ? 'opacity-60' : ''; ?>">
        
        <?php if ($estaVazio): ?>
            <div class="mb-4 inline-flex items-center gap-2 px-3 py-1 bg-red-50 border border-red-100 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                <span class="text-[9px] font-black uppercase tracking-widest text-red-600">Esgotado</span>
            </div>
        <?php endif; ?>

        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="font-serif text-3xl italic mb-1"><?php echo htmlspecialchars($produto->name); ?></h3>
                <p class="text-stone-400 text-xs uppercase tracking-widest font-bold">
                    Estoque: <?php echo $produto->storage; ?> unid.
                </p>
            </div>
            <p class="font-serif text-xl text-stone-900">R$ <?php echo number_format($produto->price, 2, ',', '.'); ?></p>
        </div>

        <p class="text-stone-500 text-sm leading-relaxed mb-8 line-clamp-2">
            <?php echo htmlspecialchars($produto->description); ?>
        </p>

        <div class="flex justify-between items-center">
            <?php if ($estaVazio): ?>
                <button disabled class="p-4 bg-stone-100 text-stone-300 rounded-2xl cursor-not-allowed border border-stone-200">
                    <i data-lucide="slash" class="w-5 h-5"></i>
                </button>
            <?php else: ?>
                <a href="carrinho_logica.php?adicionar=<?php echo $produto->id; ?>" 
                    class="p-4 bg-stone-900 text-white rounded-2xl hover:scale-110 active:scale-95 transition-all shadow-lg shadow-stone-200">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['carrinho'])): ?>
<div class="fixed bottom-8 right-8 w-80 bg-white border border-stone-200 shadow-2xl rounded-[2rem] p-6 z-[60]">
    <h3 class="font-serif text-xl mb-4 italic text-stone-800 flex items-center gap-2">
        <i data-lucide="shopping-bag" class="w-5 h-5"></i> Seu Carrinho
    </h3>
    
    <div class="max-h-60 overflow-y-auto mb-4">
        <?php 
        $totalGeral = 0;
        foreach ($_SESSION['carrinho'] as $item): 
            $subtotal = $item['preco'] * $item['quantidade'];
            $totalGeral += $subtotal;
        ?>
            <div class="flex justify-between text-sm mb-2 pb-2 border-b border-stone-50">
                <span><?php echo $item['quantidade']; ?>x <?php echo $item['nome']; ?></span>
                <span class="font-bold text-stone-600">R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="flex justify-between items-center mb-6">
        <span class="text-[10px] font-black uppercase tracking-widest text-stone-400">Total</span>
        <span class="text-xl font-serif">R$ <?php echo number_format($totalGeral, 2, ',', '.'); ?></span>
    </div>

    <form action="carrinho_logica.php" method="POST">
        <input type="hidden" name="total_pedido" value="<?php echo $totalGeral; ?>">
        <button name="finalizar_pedido" type="submit" class="w-full py-4 bg-green-600 text-white font-black uppercase tracking-widest text-[10px] rounded-xl hover:bg-green-700 transition-all">
            Lançar Pedido Agora
        </button>
    </form>
    
    <a href="?limpar_carrinho=1" class="block text-center mt-3 text-[9px] text-stone-400 uppercase tracking-tighter">Limpar Carrinho</a>
</div>
<?php endif; ?>
    </div>
    </main>
    
    <?php include 'rodape.php' ?>

    <script>
        // Inicializa os ícones
        lucide.createIcons();

        // Faz o erro sumir após 5 segundos
        const erroBox = document.querySelector('.animate-pulse'); // Corrigido para o seletor usado no seu HTML
        if (erroBox) {
            setTimeout(() => {
                erroBox.style.transition = "opacity 0.8s ease, transform 0.8s ease";
                erroBox.style.opacity = "0";
                erroBox.style.transform = "translateY(-10px)";
                setTimeout(() => erroBox.remove(), 800);
            }, 5000);
        }
    </script>
</body>

</html>