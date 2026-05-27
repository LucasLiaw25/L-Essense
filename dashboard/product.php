<?php
// dashboard/product.php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CORREÇÃO: Ajustado para 'verificarADM.php' com "v" minúsculo para evitar erros em servidores Linux
require_once __DIR__ . '/../auth/verificarADM.php';
require_once __DIR__ . '/../auth/Conexao.php';

$message = "";

// Lógica de Upload de Fotos
function handleImageUpload(): ?string {
    if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $targetDir = __DIR__ . '/../uploads/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $fileExtension = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
    $newFileName = bin2hex(random_bytes(16)) . '.' . $fileExtension;
    $targetFile = $targetDir . $newFileName;
    
    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $targetFile)) {
        return $newFileName;
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0.0;
    $storage = isset($_POST['storage']) ? (int)$_POST['storage'] : 0;

    if ($action === 'create' && !empty($name)) {
        $img = handleImageUpload();
        $sql = "INSERT INTO produtos (nome, descricao, preco, estoque, imagem) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "ssdis", $name, $description, $price, $storage, $img);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Item adicionado com sucesso!";
        }
        mysqli_stmt_close($stmt);
    } elseif ($action === 'update' && $id > 0) {
        $img = handleImageUpload();
        if ($img) {
            $sql = "UPDATE produtos SET nome=?, descricao=?, preco=?, estoque=?, imagem=? WHERE id=?";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "ssdisi", $name, $description, $price, $storage, $img, $id);
        } compression: {
            $sql = "UPDATE produtos SET nome=?, descricao=?, preco=?, estoque=? WHERE id=?";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "ssdii", $name, $description, $price, $storage, $id);
        }
        if (mysqli_stmt_execute($stmt)) {
            $message = "Item atualizado com sucesso!";
        }
        mysqli_stmt_close($stmt);
    } elseif ($action === 'delete' && $id > 0) {
        $sql = "DELETE FROM produtos WHERE id = ?";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Item removido com sucesso!";
        }
        mysqli_stmt_close($stmt);
    }
}

// Buscar todos os produtos
$products = [];
$result = mysqli_query($conexao, "SELECT * FROM produtos ORDER BY nome ASC");
if ($result) {
    $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Produtos - L-Essense</title>
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
<body class="bg-stone-50 text-stone-900 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 py-6">
        
        <?php include __DIR__ . '/../user/menu.php'; ?>

        <?php if (!empty($message)): ?>
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-bold uppercase tracking-wider flex items-center gap-2 shadow-sm animate-fade-in">
                <i data-lucide="check-circle" class="w-4 h-4"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <div class="bg-white border border-stone-200/80 rounded-3xl p-6 shadow-sm sticky top-28">
                <div class="flex justify-between items-center mb-6">
                    <h2 id="formTitle" class="font-serif text-2xl text-stone-950">Novo Registro</h2>
                    <button id="btnCancel" onclick="resetForm()" class="hidden text-[10px] font-black uppercase tracking-widest text-stone-400 hover:text-red-500 transition-all">Cancelar</button>
                </div>

                <form id="productForm" action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="productId" value="">

                    <div>
                        <label class="text-[10px] font-black uppercase text-stone-400 ml-1 mb-1 block">Nome do Item</label>
                        <input type="text" name="name" id="pName" required class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:outline-none focus:border-stone-400 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label class="text-[10px] font-black uppercase text-stone-400 ml-1 mb-1 block">Descrição / Detalhes</label>
                        <textarea name="description" id="pDesc" rows="3" class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:outline-none focus:border-stone-400 focus:bg-white transition-all resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black uppercase text-stone-400 ml-1 mb-1 block">Preço (R$)</label>
                            <input type="number" name="price" id="pPrice" step="0.01" required class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:outline-none focus:border-stone-400 focus:bg-white transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase text-stone-400 ml-1 mb-1 block">Estoque Inicial</label>
                            <input type="number" name="storage" id="pStorage" required class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:outline-none focus:border-stone-400 focus:bg-white transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black uppercase text-stone-400 ml-1 mb-1 block">Imagem do Produto</label>
                        <input type="file" name="imagem" accept="image/*" class="w-full text-xs text-stone-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-wider file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200 file:transition-all cursor-pointer">
                    </div>

                    <button type="submit" class="w-full py-4 bg-stone-900 text-white font-black uppercase tracking-widest text-[10px] rounded-xl hover:bg-black transition-all shadow-md pt-4">
                        Salvar Registro
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white border border-stone-200/80 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-stone-100 flex justify-between items-center">
                    <h2 class="font-serif text-2xl text-stone-950">Cardápio / Produtos</h2>
                    <span class="text-[10px] font-black uppercase tracking-wider bg-stone-100 px-3 py-1.5 rounded-full text-stone-600">Total: <?php echo count($products); ?></span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-stone-100 bg-stone-50/50 text-[10px] font-black uppercase tracking-wider text-stone-400">
                                <th class="py-4 px-6">Item</th>
                                <th class="py-4 px-4">Preço</th>
                                <th class="py-4 px-4">Estoque</th>
                                <th class="py-4 px-6 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-sm">
                            <?php foreach ($products as $item): ?>
                                <tr class="hover:bg-stone-50/40 transition-all">
                                    <td class="py-4 px-6 flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-xl bg-stone-100 border border-stone-200/60 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                            <?php if (!empty($item['imagem'])): ?>
                                                <img src="../uploads/<?php echo $item['imagem']; ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <i data-lucide="image" class="w-5 h-5 text-stone-300"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <span class="font-bold text-stone-900 block"><?php echo htmlspecialchars($item['nome']); ?></span>
                                            <span class="text-xs text-stone-400 max-w-xs block truncate"><?php echo htmlspecialchars($item['descricao'] ?? ''); ?></span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 font-medium text-stone-950">
                                        R$ <?php echo number_format((float)$item['preco'], 2, ',', '.'); ?>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="px-2.5 py-1 rounded-md text-xs font-bold <?php echo $item['estoque'] > 5 ? 'bg-stone-100 text-stone-700' : 'bg-red-50 text-red-700'; ?>">
                                            <?php echo $item['estoque']; ?> un
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick='editProduct(<?php echo json_encode($item, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="h-8 w-8 rounded-lg border border-stone-200 flex items-center justify-center text-stone-500 hover:text-stone-900 hover:bg-stone-50 transition-all">
                                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                            <form action="" method="POST" onsubmit="return confirm('Deseja realmente excluir este item?');" class="inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" class="h-8 w-8 rounded-lg border border-stone-200 flex items-center justify-center text-stone-400 hover:text-red-600 hover:bg-red-100 transition-all">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/../user/rodape.php'; ?>

    <script>
        lucide.createIcons();
        function editProduct(item) {
            document.getElementById('formAction').value = 'update';
            document.getElementById('productId').value = item.id;
            document.getElementById('pName').value = item.nome;
            document.getElementById('pDesc').value = item.descricao;
            document.getElementById('pPrice').value = item.preco;
            document.getElementById('pStorage').value = item.estoque;
            document.getElementById('formTitle').innerText = 'Editar Item #' + item.id;
            document.getElementById('btnCancel').classList.remove('hidden');
        }
        function resetForm() {
            document.getElementById('productForm').reset();
            document.getElementById('formAction').value = 'create';
            document.getElementById('productId').value = '';
            document.getElementById('formTitle').innerText = 'Novo Registro';
            document.getElementById('btnCancel').classList.add('hidden');
        }
    </script>
</body>
</html>