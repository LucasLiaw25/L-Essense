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

$sql = "SELECT * FROM produtos WHERE estoque > 0 ORDER BY fixado DESC, nome ASC";
$resultado = mysqli_query($conexao, $sql);
$produtos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Principal</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Instrument Serif', serif; }
        
        /* Efeito de brilho pulsante elegante nas sombras para capturar a atenção */
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); }
            50% { box-shadow: 0 10px 20px -2px rgba(217, 119, 6, 0.12), 0 4px 6px -2px rgba(217, 119, 6, 0.08); }
        }
        .promo-glow { animation: pulseGlow 2.5s infinite ease-in-out; }
    </style>
</head>
<body class="bg-stone-50 text-stone-900 min-h-screen">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <?php include __DIR__ . '/menu.php'; ?>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">
            
            <div class="lg:col-span-3">
                <div class="mb-8">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400 block mb-1">Menu Exclusivo</span>
                    <h1 class="font-serif text-4xl sm:text-5xl text-stone-950">Nossas Especialidades</h1>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php if (empty($produtos)): ?>
                        <p class="text-stone-500 text-sm italic col-span-full py-8 text-center">Nenhum produto disponível no momento.</p>
                    <?php else: ?>
                        <?php foreach ($produtos as $prod): 
                            $isPromocao = (int)$prod['fixado'] === 1;
                        ?>
                            <div class="bg-white rounded-2xl p-4 transition-all flex flex-col justify-between group relative border <?php echo $isPromocao ? 'border-amber-300 shadow-md promo-glow bg-gradient-to-b from-amber-50/10 to-white' : 'border-stone-200/60 shadow-sm hover:shadow-md'; ?>">
                                
                                <?php if ($isPromocao): ?>
                                    <div class="absolute top-3 left-3 z-10 bg-stone-900 text-amber-400 font-black text-[9px] uppercase tracking-widest px-2.5 py-1 rounded-full shadow flex items-center gap-1">
                                        <i data-lucide="sparkles" class="w-3 h-3 text-amber-400"></i>
                                        Especial da Casa
                                    </div>
                                <?php endif; ?>

                                <div>
                                    <div class="w-full h-48 bg-stone-100 rounded-xl mb-4 overflow-hidden relative">
                                        <?php if (!empty($prod['imagem'])): ?>
                                            <img src="../uploads/<?php echo $prod['imagem']; ?>" alt="<?php echo htmlspecialchars($prod['nome']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center text-stone-300">
                                                <i data-lucide="utensils-crossed" class="w-8 h-8"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <h3 class="font-bold text-stone-900 text-base mb-1">
                                        <?php echo htmlspecialchars($prod['nome']); ?>
                                    </h3>
                                    <p class="text-stone-500 text-xs line-clamp-2 mb-4"><?php echo htmlspecialchars($prod['descricao'] ?? ''); ?></p>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-stone-100 mt-auto">
                                    <span class="font-serif font-bold text-lg text-stone-950">R$ <?php echo number_format((float)$prod['preco'], 2, ',', '.'); ?></span>
                                    <a href="carrinho_logica.php?adicionar=<?php echo $prod['id']; ?>" class="p-2.5 text-white rounded-xl transition-all shadow-sm active:scale-95 <?php echo $isPromocao ? 'bg-amber-500 hover:bg-amber-600' : 'bg-amber-600 hover:bg-amber-700 shadow-amber-600/10'; ?>">
                                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="lg:col-span-1 lg:sticky lg:top-24 self-start bg-white border border-stone-200 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-4">
                    <div class="flex items-center gap-2">
                        <i data-lucide="shopping-cart" class="w-4 h-4 text-stone-900"></i>
                        <h2 class="font-bold text-sm uppercase tracking-wider text-stone-900">Seu Pedido</h2>
                    </div>
                    <?php 
                    $carrinho = obterCarrinho(); 
                    if (!empty($carrinho)): 
                    ?>
                        <a href="?limpar_carrinho=1" class="text-[10px] font-black uppercase tracking-widest text-red-500 hover:text-red-700 transition-all">Limpar</a>
                    <?php endif; ?>
                </div>

                <?php 
                $totalGeral = 0;
                if (empty($carrinho)): 
                ?>
                    <div class="py-12 text-center">
                        <i data-lucide="container" class="w-8 h-8 text-stone-300 mx-auto mb-2"></i>
                        <p class="text-stone-400 text-xs font-medium">O carrinho está vazio.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4 divide-y divide-stone-100 max-h-[50vh] overflow-y-auto pr-1">
                        <?php 
                        foreach ($carrinho as $id => $item): 
                            $subtotal = $item['preco'] * $item['quantidade'];
                            $totalGeral += $subtotal;
                        ?>
                            <div class="pt-3 first:pt-0 flex justify-between items-start gap-2">
                                <div class="flex-1">
                                    <h4 class="text-xs font-bold text-stone-900 leading-tight"><?php echo htmlspecialchars($item['nome']); ?></h4>
                                    <span class="text-[11px] text-stone-500 font-medium">R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></span>
                                </div>
                                <div class="flex items-center bg-stone-100 rounded-lg p-1 gap-1.5 shrink-0">
                                    <a href="carrinho_logica.php?adicionar=<?php echo $id; ?>" class="text-stone-500 hover:text-stone-900 transition-all"><i data-lucide="plus-circle" class="w-4 h-4"></i></a>
                                    <span class="text-xs font-bold text-stone-800 px-1"><?php echo $item['quantidade']; ?></span>
                                    <a href="carrinho_logica.php?remover=<?php echo $id; ?>" class="text-stone-500 hover:text-red-500 transition-all"><i data-lucide="minus-circle" class="w-4 h-4"></i></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="flex justify-between items-center my-4 pt-4 border-t border-stone-200">
                        <span class="text-[10px] font-black uppercase text-stone-400 tracking-wider">Total</span>
                        <span class="text-xl font-serif text-stone-950 font-bold">R$ <?php echo number_format($totalGeral, 2, ',', '.'); ?></span>
                    </div>
                    
                    <form action="./carrinho_logica.php" method="POST" class="mt-2">
                        <input type="hidden" name="total_pedido" value="<?php echo $totalGeral; ?>">
                        <button name="finalizar_pedido" type="submit" class="w-full py-3.5 bg-stone-900 text-white font-black uppercase tracking-widest text-[10px] rounded-xl hover:bg-black transition-all shadow-md active:scale-[0.98]">
                            Fechar Pedido Agora
                        </button>
                    </form>
                <?php endif; ?>
            </div>

        </div>

    </div>

    <?php include __DIR__ . '/../user/rodape.php'; ?>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>