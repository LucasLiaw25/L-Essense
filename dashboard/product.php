<?php

declare(strict_types=1);
session_start();
require '../auth/VerificarADM.php';

class Product
{
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

function addProduct(Product $product)
{
    $lastProduct = end($_SESSION['listProducts']);
    $product->id = ($lastProduct === false) ? 1 : $lastProduct->id + 1;
    $_SESSION['listProducts'][] = $product;
    return $product;
}

function updateProduct(Product $productRequest, int $id)
{
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

function deleteProduct(int $id)
{
    $found = false;
    foreach ($_SESSION['listProducts'] as $index => $product) {
        if ($product->id == $id) {
            $found = true;
            unset($_SESSION['listProducts'][$index]);
            // Reindexa o array para evitar problemas com índices ausentes
            $_SESSION['listProducts'] = array_values($_SESSION['listProducts']);
            break;
        }
    }
    if ($found) {
        return "<div class='bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl mb-6 flex items-center gap-2'><i data-lucide='trash-2' class='w-4 h-4'></i> Produto removido com sucesso</div>";
    } else {
        return "<div class='bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6'>Produto não encontrado</div>";
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
    } elseif ($action === "delete") {
        $id = (int) $_POST['product_id'];
        $message = deleteProduct($id);
    }
}

// Métricas
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
    
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* Define as fontes padrão para as classes do Tailwind */
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Instrument Serif', serif !important; }
        
        /* Ajuste fino para suavizar as fontes no navegador */
        * { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
    </style>
</head>
</head>

<body class="text-stone-900 antialiased">
    <div class="max-w-6xl mx-auto px-6">
        <?php require '../user/menu.php'; ?>
    </div>

    <div class="max-w-6xl mx-auto px-4 py-12">

        <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
            <div>
                <h1 class="text-5xl font-serif font-bold tracking-tight text-stone-900">Gestão de Produtos</h1>
                <p class="text-stone-500 mt-2 font-medium">Controle de estoque com precisão e elegância.</p>
            </div>
            <div class="flex items-center gap-2 text-stone-400 bg-white border border-stone-100 px-4 py-2 rounded-2xl shadow-sm">
                <i data-lucide="calendar" class="w-4 h-4"></i>
                <span class="text-xs font-bold uppercase tracking-widest"><?= date('d/m/Y') ?></span>
            </div>
        </header>

        <?= $message ?>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-12">
            <div class="bg-white border border-stone-200 p-6 rounded-[2rem] shadow-sm">
                <div class="w-12 h-12 bg-stone-50 rounded-2xl flex items-center justify-center mb-4 border border-stone-100">
                    <i data-lucide="package" class="w-6 h-6 text-stone-600"></i>
                </div>
                <p class="text-4xl font-black text-stone-900"><?= $totalCount ?></p>
                <p class="text-[10px] font-black uppercase tracking-[0.15em] text-stone-400 mt-1">Total de Itens</p>
            </div>
            <div class="bg-white border border-stone-200 p-6 rounded-[2rem] shadow-sm">
                <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center mb-4 border border-emerald-100">
                    <i data-lucide="check-circle" class="w-6 h-6 text-emerald-600"></i>
                </div>
                <p class="text-4xl font-black text-emerald-900"><?= $ativosCount ?></p>
                <p class="text-[10px] font-black uppercase tracking-[0.15em] text-emerald-500 mt-1">Em Linha</p>
            </div>
            <div class="bg-white border border-stone-200 p-6 rounded-[2rem] shadow-sm">
                <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center mb-4 border border-amber-100">
                    <i data-lucide="trending-up" class="w-6 h-6 text-amber-600"></i>
                </div>
                <p class="text-4xl font-black text-amber-900"><?= $baixoEstoqueCount ?></p>
                <p class="text-[10px] font-black uppercase tracking-[0.15em] text-amber-500 mt-1">Reposição</p>
            </div>
            <div class="bg-white border border-stone-200 p-6 rounded-[2rem] shadow-sm">
                <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center mb-4 border border-red-100">
                    <i data-lucide="archive" class="w-6 h-6 text-red-600"></i>
                </div>
                <p class="text-4xl font-black text-red-900"><?= $esgotadosCount ?></p>
                <p class="text-[10px] font-black uppercase tracking-[0.15em] text-red-500 mt-1">Esgotados</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-[380px_1fr] gap-10 items-start">

            <aside class="sticky top-8">
                <div class="bg-white border border-stone-200 rounded-[2.5rem] p-8 shadow-sm">
                    <h2 class="text-2xl font-serif font-bold mb-8 text-stone-800">Novo Registro</h2>

                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="action" id="formAction" value="create">

                        <div class="group">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400 mb-2 block px-1">ID para Atualização</label>
                            <input type="number" name="product_id" placeholder="Opcional" class="w-full bg-stone-50 border-stone-200 rounded-2xl p-4 text-sm focus:ring-4 focus:ring-stone-100 outline-none transition-all border group-hover:border-stone-300">
                        </div>

                        <div class="group">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400 mb-2 block px-1">Nome do Produto</label>
                            <input type="text" name="product_name" required placeholder="Ex: Relógio de Couro" class="w-full bg-stone-50 border-stone-200 rounded-2xl p-4 text-sm focus:ring-4 focus:ring-stone-100 outline-none transition-all border group-hover:border-stone-300">
                        </div>

                        <div class="group">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400 mb-2 block px-1">Descrição Detalhada</label>
                            <textarea name="product_description" rows="3" placeholder="Descreva as características..." class="w-full bg-stone-50 border-stone-200 rounded-2xl p-4 text-sm focus:ring-4 focus:ring-stone-100 outline-none transition-all border resize-none group-hover:border-stone-300"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="group">
                                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400 mb-2 block px-1">Preço</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-stone-400 text-xs font-bold">R$</span>
                                    <input type="text" name="product_price" required placeholder="0,00" class="w-full bg-stone-50 border-stone-200 rounded-2xl py-4 pl-10 pr-4 text-sm focus:ring-4 focus:ring-stone-100 outline-none transition-all border">
                                </div>
                            </div>
                            <div class="group">
                                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400 mb-2 block px-1">Unidades</label>
                                <input type="number" name="product_storage" required placeholder="0" class="w-full bg-stone-50 border-stone-200 rounded-2xl p-4 text-sm focus:ring-4 focus:ring-stone-100 outline-none transition-all border">
                            </div>
                        </div>

                        <div class="pt-6 space-y-3">
                            <button type="submit" onclick="document.getElementById('formAction').value='create'" class="w-full bg-stone-900 hover:bg-black text-stone-50 font-bold py-5 rounded-2xl transition-all flex items-center justify-center gap-3 shadow-lg shadow-stone-200">
                                <i data-lucide="plus" class="w-5 h-5"></i> Cadastrar
                            </button>
                            <button type="submit" onclick="document.getElementById('formAction').value='update'" class="w-full bg-white border border-stone-200 hover:bg-stone-50 text-stone-600 font-bold py-4 rounded-2xl transition-all">
                                Atualizar Existente
                            </button>
                        </div>
                    </form>
                </div>
            </aside>

            <main class="space-y-6">
                <div class="bg-white border border-stone-200 rounded-[2.5rem] shadow-sm overflow-hidden">
                    <div class="p-8 border-b border-stone-100 flex justify-between items-center">
                        <h2 class="text-2xl font-serif font-bold text-stone-800">Catálogo Atual</h2>
                        <span class="text-[10px] font-black text-stone-400 uppercase tracking-widest bg-stone-50 px-3 py-1 rounded-full border border-stone-100"><?= $totalCount ?> Itens</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-stone-50/50 text-[10px] font-black uppercase tracking-[0.2em] text-stone-400">
                                    <th class="px-8 py-5">Item & Ref</th>
                                    <th class="px-8 py-5">Valor</th>
                                    <th class="px-8 py-5 text-center">Estoque</th>
                                    <th class="px-8 py-5">Status</th>
                                    <th class="px-8 py-5 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                <?php foreach ($_SESSION['listProducts'] as $p): ?>
                                    <tr class="group hover:bg-stone-50/30 transition-colors">
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 bg-stone-100 rounded-xl flex items-center justify-center text-stone-400 group-hover:bg-white transition-colors border border-transparent group-hover:border-stone-100">
                                                    <i data-lucide="tag" class="w-5 h-5"></i>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-stone-900">#<?= $p->id ?> - <?= htmlspecialchars($p->name) ?></p>
                                                    <p class="text-xs text-stone-400 italic line-clamp-1 max-w-[180px]"><?= htmlspecialchars($p->description) ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 font-bold text-stone-700">
                                            R$ <?= number_format($p->price, 2, ',', '.') ?>
                                        </td>
                                        <td class="px-8 py-6 text-center">
                                            <?php if ($p->storage == 0): ?>
                                                <span class="inline-flex px-3 py-1 rounded-lg bg-red-50 text-red-600 text-[10px] font-black uppercase border border-red-100">Esgotado</span>
                                            <?php elseif ($p->storage <= 10): ?>
                                                <span class="inline-flex px-3 py-1 rounded-lg bg-amber-50 text-amber-600 text-[10px] font-black uppercase border border-amber-100"><?= $p->storage ?> un.</span>
                                            <?php else: ?>
                                                <span class="inline-flex px-3 py-1 rounded-lg bg-stone-100 text-stone-600 text-[10px] font-black uppercase"><?= $p->storage ?> un.</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-8 py-6">
                                            <?php if ($p->storage > 0): ?>
                                                <div class="flex items-center gap-2 text-emerald-600 font-bold text-[10px] uppercase tracking-wider">
                                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Disponível
                                                </div>
                                            <?php else: ?>
                                                <div class="flex items-center gap-2 text-stone-300 font-bold text-[10px] uppercase tracking-wider">
                                                    <span class="w-2 h-2 rounded-full bg-stone-200"></span> Inativo
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-8 py-6 text-right">
                                            <div class="flex justify-end items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este produto?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="product_id" value="<?= $p->id ?>">
                                                    <button type="submit" class="p-3 text-stone-400 btn-delete rounded-xl transition-all">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (empty($_SESSION['listProducts'])): ?>
                                    <tr>
                                        <td colspan="5" class="px-8 py-24 text-center">
                                            <div class="flex flex-col items-center gap-4 text-stone-300">
                                                <div class="w-16 h-16 bg-stone-50 rounded-full flex items-center justify-center">
                                                    <i data-lucide="box" class="w-8 h-8"></i>
                                                </div>
                                                <p class="font-serif text-xl italic text-stone-400">O catálogo está vazio no momento.</p>
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
        // Inicializa os ícones
        lucide.createIcons();

        // Faz o erro sumir após 5 segundos
        const erroBox = document.querySelector('.bg-red-50, .bg-emerald-50, .bg-amber-50, .bg-sky-50');
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