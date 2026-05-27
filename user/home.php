<?php
// user/home.php
require_once __DIR__ . '/../auth/VerificarLogin.php';
require_once __DIR__ . '/../auth/Conexao.php';

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

if (isset($_GET['limpar_carrinho'])) {
    $_SESSION['carrinho'] = [];
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>body { font-family: 'Inter', sans-serif; } .font-serif { font-family: 'Instrument Serif', serif; }</style>
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
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($produtos as $p): ?>
                        <div class="bg-white border border-stone-200/60 p-5 rounded-2xl shadow-sm flex flex-col justify-between gap-4 hover:shadow-md transition-all">
                            <div class="flex gap-4">
                                <?php if (!empty($p['imagem']) && file_exists(__DIR__ . '/../uploads/' . $p['imagem'])): ?>
                                    <img src="../uploads/<?php echo htmlspecialchars($p['imagem']); ?>" class="w-20 h-20 object-cover rounded-xl border border-stone-100 shrink-0">
                                <?php else: ?>
                                    <div class="w-20 h-20 bg-stone-50 border border-stone-200/60 rounded-xl flex items-center justify-center text-stone-300 shrink-0"><i data-lucide="utensils" class="w-5 h-5"></i></div>
                                <?php endif; ?>
                                <div>
                                    <h3 class="font-bold text-stone-900 text-sm"><?php echo htmlspecialchars($p['nome']); ?></h3>
                                    <p class="text-xs text-stone-400 mt-1 line-clamp-2 font-medium"><?php echo htmlspecialchars($p['descricao'] ?? 'Sem descrição.'); ?></p>
                                </div>
                            </div>
                            <div class="flex justify-between items-center pt-3 border-t border-stone-100">
                                <span class="font-mono font-bold text-sm text-stone-800">R$ <?php echo number_format((float)$p['preco'], 2, ',', '.'); ?></span>
                                <a href="../dashboard/carrinho_logica.php?adicionar=<?php echo $p['id']; ?>" class="bg-stone-900 text-white font-black text-[10px] uppercase tracking-widest px-4 py-2 rounded-xl hover:bg-black transition-all">
                                    Adicionar
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="lg:col-span-1 bg-white border border-stone-200/60 p-6 rounded-2xl shadow-sm h-fit sticky top-24">
                <h2 class="text-xs font-black uppercase tracking-widest text-stone-400 border-b border-stone-100 pb-3 mb-4">Seu Pedido</h2>
                <?php if (empty($_SESSION['carrinho'])): ?>
                    <div class="text-center py-8 text-stone-400 italic text-xs">
                        <i data-lucide="shopping-bag" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                        Carrinho vazio.
                    </div>
                <?php else: 
                    $totalGeral = 0;
                    foreach ($_SESSION['carrinho'] as $id => $item): 
                        $subtotal = $item['preco'] * $item['quantidade'];
                        $totalGeral += $subtotal;
                ?>
                        <div class="flex justify-between items-center text-xs border-b border-stone-100 py-2.5">
                            <div>
                                <span class="font-bold text-stone-900"><?php echo $item['quantidade']; ?>x</span> <?php echo htmlspecialchars($item['nome']); ?>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-stone-600 font-medium">R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                                <a href="../dashboard/carrinho_logica.php?remover=<?php echo $id; ?>" class="text-stone-300 hover:text-red-500 transition-all"><i data-lucide="minus-circle" class="w-4 h-4"></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="flex justify-between items-center my-4 pt-2">
                        <span class="text-[10px] font-black uppercase text-stone-400 tracking-wider">Total</span>
                        <span class="text-xl font-serif text-stone-950 font-bold">R$ <?php echo number_format($totalGeral, 2, ',', '.'); ?></span>
                    </div>
                    <form action="../dashboard/carrinho_logica.php" method="POST">
                        <input type="hidden" name="total_pedido" value="<?php echo $totalGeral; ?>">
                        <button name="finalizar_pedido" type="submit" class="w-full py-3.5 bg-stone-900 text-white font-black uppercase tracking-widest text-[10px] rounded-xl hover:bg-black transition-all shadow-md">
                            Fechar Pedido Agora
                        </button>
                    </form>
                    <a href="?limpar_carrinho=1" class="block text-center mt-3 text-[9px] text-stone-400 uppercase tracking-widest font-bold hover:text-red-500 transition-all">Limpar Tudo</a>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php include 'rodape.php'; ?>
    <script>lucide.createIcons();</script>
</body>
</html>