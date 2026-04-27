<?php
// Definição da classe Product (necessária para ler a sessão sem erro)
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

// --- LOGICA DE ATUALIZAÇÃO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_status'])) {
    $index = $_POST['pedido_index'];
    $novo_status = $_POST['novo_status'];

    if (isset($_SESSION['pedido_realizados'][$index])) {
        $statusAntigo = $_SESSION['pedido_realizados'][$index]['status'] ?? 'Pendente';

        // Baixa o estoque se mudar para Concluído agora
        if ($novo_status === 'Concluído' && $statusAntigo !== 'Concluído') {
            foreach ($_SESSION['pedido_realizados'][$index]['itens'] as $item) {
                if (isset($_SESSION['listProducts'])) {
                    foreach ($_SESSION['listProducts'] as $prod) {
                        if ($prod->name === $item['nome']) {
                            $prod->storage -= $item['quantidade'];
                            if ($prod->storage < 0) $prod->storage = 0;
                            break;
                        }
                    }
                }
            }
        }
        $_SESSION['pedido_realizados'][$index]['status'] = $novo_status;
        header("Location: status.php?sucesso=1");
        exit();
    }
}

$pedidos = $_SESSION['pedido_realizados'] ?? [];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L-Essense - Status dos Pedidos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Instrument Serif', serif; }
    </style>
</head>
<body class="bg-stone-50 text-stone-900 antialiased">
    <div class="max-w-6xl mx-auto px-6 pb-20">
        <?php include '../user/menu.php'; ?>

        <header class="mb-12">
            <h1 class="font-serif text-5xl italic mb-2">Meus Pedidos</h1>
            <p class="text-stone-400 text-[10px] font-black uppercase tracking-[0.2em]">Acompanhe as suas experiências gastronómicas</p>
        </header>

        <?php if (empty($pedidos)): ?>
            <div class="bg-white border border-stone-200 rounded-[3rem] p-20 text-center shadow-sm">
                <i data-lucide="utensils" class="w-12 h-12 text-stone-200 mx-auto mb-6"></i>
                <p class="font-serif text-2xl italic text-stone-400">Ainda não realizou nenhum pedido.</p>
                <a href="../user/home.php" class="inline-block mt-8 px-8 py-4 bg-stone-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:scale-105 transition-all">Explorar Menu</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <?php foreach (array_reverse($pedidos, true) as $index => $pedido): ?>
                    <div class="bg-white border border-stone-100 rounded-[2.5rem] overflow-hidden shadow-sm hover:shadow-xl transition-all duration-500 flex flex-col">
                        
                        <div class="p-8 flex-1">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-stone-300 block mb-1">Pedido #<?php echo $index + 1; ?></span>
                                    <h2 class="font-serif text-2xl text-stone-800 italic"><?php echo $pedido['data']; ?></h2>
                                </div>
                                <div class="bg-stone-50 px-4 py-2 rounded-xl border border-stone-100">
                                    <span class="text-xs font-bold text-stone-900">R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></span>
                                </div>
                            </div>

                            <div class="space-y-4 mb-8">
                                <h3 class="text-[9px] font-black uppercase tracking-[0.2em] text-stone-400 border-b border-stone-50 pb-2">Itens Solicitados</h3>
                                <?php foreach ($pedido['itens'] as $item): ?>
                                    <div class="flex justify-between items-center text-sm">
                                        <div class="flex items-center gap-3">
                                            <span class="w-6 h-6 bg-stone-100 rounded-lg flex items-center justify-center text-[10px] font-black text-stone-500"><?php echo $item['quantidade']; ?>x</span>
                                            <span class="text-stone-600 font-medium"><?php echo htmlspecialchars($item['nome']); ?></span>
                                        </div>
                                        <span class="text-stone-300 italic text-xs">R$ <?php echo number_format($item['preco'] * $item['quantidade'], 2, ',', '.'); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-stone-50">
                                <span class="text-[10px] font-bold text-stone-400 uppercase">Cliente: <?php echo htmlspecialchars($pedido['usuario']); ?></span>
                                
                                <?php 
                                    $status = $pedido['status'] ?? 'Pendente';
                                    $corStatus = match($status) {
                                        'Concluído' => 'bg-green-50 text-green-600 border-green-100',
                                        'Cancelado' => 'bg-red-50 text-red-600 border-red-100',
                                        default => 'bg-amber-50 text-amber-600 border-amber-100'
                                    };
                                ?>
                                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border <?php echo $corStatus; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($_SESSION['perfil'] === 'admin'): ?>
                            <div class="bg-stone-900 p-6">
                                <form method="POST" class="flex gap-3">
                                    <input type="hidden" name="pedido_index" value="<?php echo $index; ?>">
                                    <select name="novo_status" class="flex-1 bg-stone-800 text-stone-300 border-none rounded-xl px-4 py-3 text-[10px] font-black uppercase tracking-widest outline-none focus:ring-1 focus:ring-stone-600">
                                        <option value="Pendente" <?php echo $status == 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
                                        <option value="Concluído" <?php echo $status == 'Concluído' ? 'selected' : ''; ?>>Concluído</option>
                                        <option value="Cancelado" <?php echo $status == 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                    <button type="submit" name="alterar_status" class="bg-white text-stone-900 px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-stone-200 transition-all">
                                        Atualizar
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../user/rodape.php'; ?>
    <script>lucide.createIcons();</script>
</body>
</html>