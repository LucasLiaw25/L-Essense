<?php
declare(strict_types = 1);
session_start();

class Product {
    public int $id;
    public function __construct(
        public string $name,
        public string $description,
        public float $price,
        public int $storage
    ) {}
}

if (!isset($_SESSION['listProducts'])) {
    $_SESSION['listProducts'] = [];
}

$message = "";

function addProduct(Product $product) {
    $lastProduct = end($_SESSION['listProducts']);
    $product->id = ($lastProduct === false) ? 1 : $lastProduct->id + 1;
    $_SESSION['listProducts'][] = $product;
    return $product;
}

function updateProduct(Product $productRequest, int $id) {
    $found = false;
    foreach ($_SESSION['listProducts'] as $product) {
        if ($product->id == $id) {
            if (!empty($productRequest->name)) $product->name = $productRequest->name;
            if (!empty($productRequest->description)) $product->description = $productRequest->description;
            if ($productRequest->price > 0) $product->price = $productRequest->price;
            if ($productRequest->storage >= 0) $product->storage = $productRequest->storage;
            $found = true;
            break;
        }
    }
    return $found;
}

function deleteProduct(int $id){
    $found = false;
    foreach($_SESSION['listProducts'] as $product){
        if($product->id == $id){
            $found = true;
            $_SESSION['listProducts'].deleteProduct($product);
        }
    }
    if($found){
        return "Produto deletado com sucesso";
    }else{
        return "Produto não encontrado";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'create') {
        if (empty($_POST['product_name']) || empty($_POST['product_description']) || empty($_POST['product_price'])) {
            $message = "<div class='bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6'>Erro: Preencha os campos obrigatórios!</div>";
        } else {
            $product = new Product(
                $_POST['product_name'],
                $_POST['product_description'],
                (float) str_replace(',', '.', $_POST['product_price']),
                (int) $_POST['product_storage']
            );
            addProduct($product);
            $message = "<div class='bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl mb-6'>Produto cadastrado com sucesso!</div>";
        }
    
    } elseif ($action === 'update') {
        $id = (int) $_POST['product_id'];
        $productReq = new Product(
            $_POST['product_name'] ?? '',
            $_POST['product_description'] ?? '',
            (float) str_replace(',', '.', ($_POST['product_price'] ?? '0')),
            (int) ($_POST['product_storage'] ?? 0)
        );
        if (updateProduct($productReq, $id)) {
            $message = "<div class='bg-sky-50 border border-sky-200 text-sky-800 px-4 py-3 rounded-xl mb-6'>Produto #$id atualizado com sucesso!</div>";
        } else {
            $message = "<div class='bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6'>Produto #$id não encontrado!</div>";
        }
    }
    elseif($action === "delete"){
        $id = (int) $_POST("")
    }
}

$totalCount = count($_SESSION['listProducts']);
$ativosCount = 0;
$baixoEstoqueCount = 0;
$esgotadosCount = 0;

foreach ($_SESSION['listProducts'] as $p) {
    if ($p->storage > 0) $ativosCount++;
    if ($p->storage > 0 && $p->storage <= 10) $baixoEstoqueCount++;
    if ($p->storage == 0) $esgotadosCount++;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Produtos | Estilo Boutique</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif&family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #fafaf9; }
        .font-serif { font-family: 'Instrument Serif', serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e7e5e4; border-radius: 10px; }
    </style>
</head>
<body class="text-stone-900 antialiased">

<div class="max-w-6xl mx-auto px-4 py-12">
    
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
        <div>
            <h1 class="text-5xl font-serif font-bold tracking-tight">Gestão de Produtos</h1>
            <p class="text-stone-500 mt-2">Administre seu catálogo com elegância e precisão.</p>
        </div>
        <div class="flex items-center gap-2 text-stone-400">
            <i data-lucide="calendar" class="w-4 h-4"></i>
            <span class="text-sm font-medium"><?= date('d/m/Y') ?></span>
        </div>
    </header>

    <?= $message ?>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-stone-50 border border-stone-100 p-5 rounded-2xl">
            <div class="w-10 h-10 bg-stone-100 rounded-xl flex items-center justify-center mb-3">
                <i data-lucide="package" class="w-5 h-5 text-stone-600"></i>
            </div>
            <p class="text-3xl font-black text-stone-800"><?= $totalCount ?></p>
            <p class="text-xs font-bold uppercase tracking-wider text-stone-500 mt-1">Total</p>
        </div>
        <div class="bg-emerald-50 border border-emerald-100 p-5 rounded-2xl">
            <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center mb-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
            </div>
            <p class="text-3xl font-black text-emerald-800"><?= $ativosCount ?></p>
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-600 mt-1">Ativos</p>
        </div>
        <div class="bg-amber-50 border border-amber-100 p-5 rounded-2xl">
            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center mb-3">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600"></i>
            </div>
            <p class="text-3xl font-black text-amber-800"><?= $baixoEstoqueCount ?></p>
            <p class="text-xs font-bold uppercase tracking-wider text-amber-600 mt-1">Est. Baixo</p>
        </div>
        <div class="bg-red-50 border border-red-100 p-5 rounded-2xl">
            <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center mb-3">
                <i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i>
            </div>
            <p class="text-3xl font-black text-red-800"><?= $esgotadosCount ?></p>
            <p class="text-xs font-bold uppercase tracking-wider text-red-600 mt-1">Esgotados</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-[380px_1fr] gap-8">
        
        <aside>
            <div class="bg-white border border-stone-200 rounded-3xl p-6 shadow-sm sticky top-8">
                <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i> Registrar Produto
                </h2>
                
                <form method="POST" class="space-y-5">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 px-1">ID (Para Atualizar)</label>
                        <input type="number" name="product_id" placeholder="Ex: 1" class="w-full bg-stone-50 border-stone-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-stone-200 outline-none transition-all">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 px-1">Nome do Produto</label>
                        <input type="text" name="product_name" placeholder="Ex: Café Bourbon Amarelo" class="w-full bg-stone-50 border-stone-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-stone-200 outline-none transition-all">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 px-1">Descrição</label>
                        <textarea name="product_description" rows="3" placeholder="Notas de chocolate e caramelo..." class="w-full bg-stone-50 border-stone-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-stone-200 outline-none transition-all resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 px-1">Preço (R$)</label>
                            <input type="text" name="product_price" placeholder="34,90" class="w-full bg-stone-50 border-stone-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-stone-200 outline-none transition-all">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 px-1">Estoque</label>
                            <input type="number" name="product_storage" placeholder="100" class="w-full bg-stone-50 border-stone-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-stone-200 outline-none transition-all">
                        </div>
                    </div>

                    <div class="pt-4 space-y-3">
                        <button type="submit" name="action" value="create" class="w-full bg-stone-800 hover:bg-stone-900 text-stone-50 font-bold py-3.5 rounded-xl transition-all flex items-center justify-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i> Cadastrar Produto
                        </button>
                        <button type="submit" name="action" value="update" class="w-full bg-white border border-stone-200 hover:bg-stone-50 text-stone-700 font-bold py-3.5 rounded-xl transition-all">
                            Atualizar por ID
                        </button>
                    </div>
                </form>
            </div>
        </aside>

        <main class="space-y-6">
            
            <div class="relative">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400"></i>
                <input type="text" placeholder="Buscar por nome ou descrição..." class="w-full pl-11 pr-4 py-4 bg-white border border-stone-200 rounded-2xl shadow-sm outline-none focus:ring-2 focus:ring-stone-200">
            </div>

            <div class="bg-white border border-stone-200 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex flex-wrap gap-2">
                    <button class="bg-stone-800 text-stone-50 px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2">
                        <i data-lucide="layers" class="w-3 h-3"></i> Todo Estoque
                    </button>
                    <button class="bg-white border border-stone-200 text-stone-500 px-4 py-2 rounded-xl text-xs font-bold hover:bg-stone-50">Disponível</button>
                    <button class="bg-white border border-stone-200 text-stone-500 px-4 py-2 rounded-xl text-xs font-bold hover:bg-stone-50">Estoque Baixo</button>
                    <button class="bg-white border border-stone-200 text-stone-500 px-4 py-2 rounded-xl text-xs font-bold hover:bg-stone-50">Esgotado</button>
                </div>
            </div>

            <div class="bg-white border border-stone-200 rounded-3xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-stone-50/50 border-b border-stone-100 text-[10px] font-black uppercase tracking-widest text-stone-400">
                                <th class="px-6 py-5">Produto</th>
                                <th class="px-6 py-5">Preço</th>
                                <th class="px-6 py-5">Estoque</th>
                                <th class="px-6 py-5">Status</th>
                                <th class="px-6 py-5 text-right text-stone-400"><i data-lucide="more-horizontal" class="w-4 h-4 ml-auto"></i></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-50">
                            <?php foreach ($_SESSION['listProducts'] as $p): ?>
                            <tr class="group hover:bg-stone-50/40 transition-colors">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-stone-100 rounded-xl flex items-center justify-center text-stone-400">
                                            <i data-lucide="image" class="w-6 h-6"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-stone-800">#<?= $p->id ?> - <?= htmlspecialchars($p->name) ?></p>
                                            <p class="text-xs text-stone-400 line-clamp-1 max-w-[200px]"><?= htmlspecialchars($p->description) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 font-bold text-stone-700">
                                    R$ <?= number_format($p->price, 2, ',', '.') ?>
                                </td>
                                <td class="px-6 py-5">
                                    <?php if($p->storage == 0): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-50 text-red-600 text-[10px] font-black uppercase border border-red-100">
                                            Esgotado
                                        </span>
                                    <?php elseif($p->storage <= 10): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 text-amber-600 text-[10px] font-black uppercase border border-amber-100">
                                            <?= $p->storage ?> un.
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-teal-50 text-teal-600 text-[10px] font-black uppercase border border-teal-100">
                                            <?= $p->storage ?> un.
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-5 text-sm">
                                    <?php if($p->storage > 0): ?>
                                        <span class="flex items-center gap-1.5 text-emerald-600 font-bold text-xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Ativo
                                        </span>
                                    <?php else: ?>
                                        <span class="flex items-center gap-1.5 text-stone-400 font-bold text-xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-stone-300"></span> Inativo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button title="Editar" class="p-2 hover:bg-stone-200 rounded-lg text-stone-400 transition-colors"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                                        <button title="Excluir" class="p-2 hover:bg-red-50 hover:text-red-500 rounded-lg text-stone-400 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>

                            <?php if (empty($_SESSION['listProducts'])): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center gap-3 text-stone-300">
                                        <i data-lucide="package-open" class="w-12 h-12"></i>
                                        <p class="text-stone-400 font-medium">Nenhum produto no catálogo.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    lucide.createIcons();
</script>

</body>
</html>