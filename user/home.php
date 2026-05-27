<?php
// user/home.php
require_once __DIR__ . '/../auth/VerificarLogin.php';
// IMPORTANTE: Inclui a lógica baseada em Cookies que criamos acima
require_once __DIR__ . '/carrinho_logica.php'; 

global $conexao;

// Lógica de limpar tudo adaptada para Cookies
if (isset($_GET['limpar_carrinho'])) {
    limparCarrinhoCookie();
    header("Location: home.php");
    exit();
}

$sql = "SELECT * FROM produtos WHERE estoque > 0 ORDER BY nome ASC";
$resultado = mysqli_query($conexao, $sql);
$produtos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Principal - L-Essense</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
body { font-family: 'Inter', sans-serif; }
    .font-serif { font-family: 'Instrument Serif', serif; }

    /* Keyframes para Entrada Suave e Subida Progressiva */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(16px) scale(0.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* Keyframe para Efeito de Piscar Suave (Alerta/Status) */
    @keyframes subtlePulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    /* Classes de Utilidade */
    .animate-fade-in-up {
        animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    
    /* Delays de animação para efeito cascata (itens aparecendo um após o outro) */
    .animation-delay-100 { animation-delay: 100ms; }
    .animation-delay-200 { animation-delay: 200ms; }
    .animation-delay-300 { animation-delay: 300ms; }
</style>
</head>
<body class="bg-stone-50 text-stone-900 min-h-screen p-4 md:p-8">
    <main class="max-w-6xl mx-auto">
        <?php include 'menu.php'; ?>

        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'pedido_realizado'): ?>
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl mb-6 text-xs font-semibold flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i> Pedido enviado ao balcão! Acompanhe o preparo em "Pedidos".
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="border-b border-stone-200 pb-3">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400 block mb-1">Experiência Gastronômica</span>
                    <h2 class="font-serif text-3xl italic text-stone-950">Nosso Cardápio</h2>
                </div>
                
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($produtos as $prod): ?>
        <div class="bg-white border border-stone-200/80 rounded-3xl p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-md flex flex-col justify-between group">
            
            <div class="w-full h-48 rounded-2xl bg-stone-100 overflow-hidden mb-4 relative">
                <?php if (!empty($prod['imagem'])): ?>
                    <img src="../uploads/<?php echo $prod['imagem']; ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-stone-300">
                        <i data-lucide="image" class="w-8 h-8"></i>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <span class="font-serif text-xl text-stone-900 block mb-1"><?php echo htmlspecialchars($prod['nome']); ?></span>
                <p class="text-xs text-stone-400 line-clamp-2 mb-4"><?php echo htmlspecialchars($prod['descricao'] ?? ''); ?></p>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-stone-100">
                <span class="font-bold text-stone-950">R$ <?php echo number_format((float)$prod['preco'], 2, ',', '.'); ?></span>
                
                <a href="?adicionar=<?php echo $prod['id']; ?>" 
                   class="h-9 px-4 bg-stone-900 hover:bg-black text-white text-[10px] font-black uppercase tracking-wider rounded-xl flex items-center justify-center gap-1.5 transition-all duration-200 active:scale-95 shadow-sm">
                    <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i> Adicionar
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
            </div>

            <div class="flex-1 overflow-y-auto pr-2 space-y-4">
    <?php if (empty($carrinho_itens)): ?>
        <div class="text-center py-12">
            <i data-lucide="shopping-bag" class="w-8 h-8 text-stone-300 mx-auto mb-3"></i>
            <p class="text-xs font-medium text-stone-400">Seu carrinho está vazio</p>
        </div>
    <?php else: ?>
        <?php 
        $totalGeral = 0; 
        foreach ($carrinho_itens as $id => $item): 
            $subtotal = $item['preco'] * $item['quantidade'];
            $totalGeral += $subtotal;
        ?>
            <div class="flex items-center justify-between gap-4 p-3 bg-stone-50 rounded-xl border border-stone-100">
                <div class="flex-1 min-w-0">
                    <h4 class="text-xs font-bold text-stone-900 truncate"><?php echo htmlspecialchars($item['nome']); ?></h4>
                    <p class="text-[10px] text-stone-500 mt-0.5">
                        <?php echo $item['quantidade']; ?>x R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?>
                    </p>
                </div>
                <div class="flex items-center gap-2 bg-white border border-stone-200/60 rounded-lg p-1 shadow-sm">
                    <a href="carrinho_logica.php?adicionar=<?php echo $id; ?>" class="text-stone-600 hover:text-stone-900 transition-all"><i data-lucide="plus-circle" class="w-4 h-4"></i></a>
                    <span class="text-xs font-bold text-stone-800 px-1"><?php echo $item['quantidade']; ?></span>
                    <a href="carrinho_logica.php?remover=<?php echo $id; ?>" class="text-stone-300 hover:text-red-500 transition-all"><i data-lucide="minus-circle" class="w-4 h-4"></i></a>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div class="flex justify-between items-center my-4 pt-2">
            <span class="text-[10px] font-black uppercase text-stone-400 tracking-wider">Total</span>
            <span class="text-xl font-serif text-stone-950 font-bold">R$ <?php echo number_format($totalGeral, 2, ',', '.'); ?></span>
        </div>
        
        <form action="./carrinho_logica.php" method="POST">
            <input type="hidden" name="total_pedido" value="<?php echo $totalGeral; ?>">
            <button name="finalizar_pedido" type="submit" class="w-full py-3.5 bg-stone-900 text-white font-black uppercase tracking-widest text-[10px] rounded-xl hover:bg-black transition-all shadow-md">
                Fechar Pedido Agora
            </button>
        </form>
        <a href="?limpar_carrinho=1" class="block text-center mt-3 text-[9px] text-stone-400 uppercase tracking-widest font-bold hover:text-red-500 transition-all">Limpar Tudo</a>
    <?php endif; ?>
</div>
    </main>
    <?php include 'rodape.php'; ?>
    <script>lucide.createIcons();</script>
</body>
</html>