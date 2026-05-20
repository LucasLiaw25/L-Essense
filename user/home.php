<?php
require_once __DIR__ . '/../auth/VerificarLogin.php';
require_once __DIR__ .'/../auth/Conexao.php';

if(!isset($_SESSION['carrinho'])){
    $_SESSION['carrinho'] = [];
}

if(isset($_GET['limpar_carrinho'])){
    $_SESSION['carrinho'] = [];
    header("Location: home.php");
    exit();
}

$sql = "SELECT * FROM produtos";
$resultado = mysqli_query($conexao, $sql);

$produtos = [];
if($resultado){
    while($linha = mysqli_fetch_assoc($resultado)){
        $produtos[] = $linha;
    }
}
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
    </style>
</head>

<body class="bg-stone-50 text-stone-900 antialiased min-h-screen flex flex-col justify-between">

    <main class="max-w-6xl mx-auto px-4 w-full flex-grow">
        <?php include 'menu.php'; ?>

        <?php if (isset($_GET['erro']) && $_GET['erro'] === 'sem_estoque'): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 text-xs font-black uppercase tracking-widest rounded-2xl flex items-center gap-2 animate-pulse">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                Desculpe, a quantidade desejada excede o limite do nosso estoque atual!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'adicionado'): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 text-xs font-black uppercase tracking-widest rounded-2xl flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                Item adicionado com sucesso ao seu pedido!
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php if (!empty($produtos)): ?>
                    <?php foreach ($produtos as $produto): ?>
                        <div class="bg-white border border-stone-100 rounded-3xl p-6 hover:shadow-xl hover:shadow-stone-100/50 transition-all flex flex-col justify-between group relative overflow-hidden">
                            
                            <?php if ($produto['estoque'] <= 0): ?>
                                <div class="absolute top-4 right-4 bg-red-600 text-white text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-full z-10 shadow-md shadow-red-100">
                                    Esgotado
                                </div>
                            <?php endif; ?>

                            <div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-stone-400 block mb-2">Disponível</span>
                                <h3 class="font-serif text-2xl text-stone-900 mb-2 group-hover:text-amber-700 transition-colors">
                                    <?php echo htmlspecialchars($produto['nome']); ?>
                                </h3>
                                <p class="text-stone-500 text-xs leading-relaxed font-medium mb-6">
                                    <?php echo htmlspecialchars($produto['descricao']); ?>
                                </p>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-stone-50">
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black uppercase tracking-wider text-stone-400">Preço Unitário</span>
                                    <span class="text-lg font-serif text-stone-900">R$ <?php echo number_format((float)$produto['preco'], 2, ',', '.'); ?></span>
                                </div>

                                <?php if ($produto['estoque'] > 0): ?>
                                    <a href="carrinho_logica.php?adicionar=<?php echo $produto['id']; ?>" 
                                       class="p-3 bg-stone-950 text-white rounded-2xl hover:bg-amber-700 hover:scale-105 active:scale-95 transition-all shadow-md shadow-stone-900/10 flex items-center justify-center">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                    </a>
                                <?php else: ?>
                                    <button disabled class="p-3 bg-stone-100 text-stone-300 rounded-2xl cursor-not-allowed">
                                        <i data-lucide="minus-circle" class="w-4 h-4"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-stone-500 text-sm italic lg:col-span-2 text-center py-12">Nenhum prato disponível no cardápio hoje.</p>
                <?php endif; ?>
            </div>

            <?php if (!empty($_SESSION['carrinho'])): ?>
            <div class="bg-white border border-stone-100 rounded-3xl p-6 lg:sticky lg:top-24 shadow-sm">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-stone-100">
                    <div>
                        <h2 class="font-serif text-2xl text-stone-900">Seu Pedido</h2>
                        <span class="text-[9px] font-black uppercase tracking-widest text-stone-400">Itens Selecionados</span>
                    </div>
                    <i data-lucide="shopping-bag" class="w-5 h-5 text-stone-400"></i>
                </div>

                <div class="space-y-4 max-h-[350px] overflow-y-auto pr-2 mb-6">
                    <?php 
                    $totalGeral = 0;
                    foreach ($_SESSION['carrinho'] as $id => $item): 
                        $subtotal = $item['preco'] * $item['quantidade'];
                        $totalGeral += $subtotal;
                    ?>
                        <div class="flex justify-between items-start gap-4 p-3 bg-stone-50 rounded-2xl">
                            <div class="flex-1">
                                <h4 class="text-xs font-bold text-stone-800"><?php echo htmlspecialchars($item['nome']); ?></h4>
                                <span class="text-[10px] font-medium text-stone-400"><?php echo $item['quantidade']; ?>x R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></span>
                            </div>
                            <span class="text-xs font-black text-stone-900 font-serif">R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
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
        lucide.createIcons();

        const erroBox = document.querySelector('.animate-pulse');
        if (erroBox) {
            setTimeout(() => {
                erroBox.style.transition = "opacity 0.8s ease, transform 0.8s ease";
                erroBox.style.opacity = "0";
                setTimeout(() => erroBox.remove(), 800);
            }, 5000);
        }
    </script>
</body>
</html>