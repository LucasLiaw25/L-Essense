<?php
// dashboard/product.php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    $desc = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0.0);
    $storage = (int)($_POST['storage'] ?? 0);
    
    // Captura o estado do checkbox de fixação (1 para ativo, 0 para inativo)
    $fixado = isset($_POST['fixado']) ? 1 : 0;

    if ($action === 'create' && !empty($name)) {
        $img = handleImageUpload();
        $sql = "INSERT INTO produtos (nome, descricao, preco, estoque, fixado, imagem) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "ssdiis", $name, $desc, $price, $storage, $fixado, $img);
        if (mysqli_stmt_execute($stmt)) $message = "Produto cadastrado com sucesso!";
        mysqli_stmt_close($stmt);
        
    } elseif ($action === 'update' && $id > 0) {
        $img = handleImageUpload();
        if ($img) {
            $sql = "UPDATE produtos SET nome=?, descricao=?, preco=?, estoque=?, fixado=?, imagem=? WHERE id=?";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "ssdiisi", $name, $desc, $price, $storage, $fixado, $img, $id);
        } else {
            $sql = "UPDATE produtos SET nome=?, descricao=?, preco=?, estoque=?, fixado=? WHERE id=?";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "ssdiii", $name, $desc, $price, $storage, $fixado, $id);
        }
        if (mysqli_stmt_execute($stmt)) $message = "Produto atualizado com sucesso!";
        mysqli_stmt_close($stmt);
        
    } elseif ($action === 'delete' && $id > 0) {
        $sql = "DELETE FROM produtos WHERE id=?";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) $message = "Produto removido!";
        mysqli_stmt_close($stmt);
    }
}

// Buscar todos os produtos ordenando pelos fixados primeiro
$sql_all = "SELECT * FROM produtos ORDER BY fixado DESC, nome ASC";
$res = mysqli_query($conexao, $sql_all);
$list = mysqli_fetch_all($res, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - L-Essense</title>
    
    <link rel="stylesheet" href="../style.css">
    
    <script src="https://cdn.tailwindcss.com\"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/lucide@latest\"></script>
</head>
<body class="bg-stone-50 text-stone-900 antialiased font-sans">
    <style>

        body { 
    font-family: 'Inter', sans-serif; 
}
.font-serif { 
    font-family: 'Instrument Serif', serif; 
}

    </style>
</head>
<body class="bg-stone-50 text-stone-900 min-h-screen">
    
    <div class="max-w-7xl mx-auto px-4 py-8">
        <?php include '../user/menu.php' ?>
        <div class="mb-6 flex justify-between items-center">
            <div>
                
                <h1 class="text-3xl font-serif">Gerenciamento de Cardápio</h1>
            </div>
        </div>

        <?php if(!empty($message)): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl mb-6 text-sm font-medium">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <div class="bg-white border border-stone-200 rounded-2xl p-6 shadow-sm">
                <h2 id="formTitle" class="text-lg font-bold text-stone-900 mb-4">Novo Registro</h2>
                
                <form id="productForm" action="product.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="productId" value="">

                    <div>
                        <label class="text-xs font-bold uppercase text-stone-500 block mb-1">Nome do Produto</label>
                        <input type="text" name="name" id="pName" required class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm outline-none focus:border-stone-400 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase text-stone-500 block mb-1">Descrição</label>
                        <textarea name="description" id="pDesc" rows="3" class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm outline-none focus:border-stone-400 focus:bg-white transition-all"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold uppercase text-stone-500 block mb-1">Preço (R$)</label>
                            <input type="number" step="0.01" name="price" id="pPrice" required class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm outline-none focus:border-stone-400 focus:bg-white transition-all">
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-stone-500 block mb-1">Estoque</label>
                            <input type="number" name="storage" id="pStorage" required class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm outline-none focus:border-stone-400 focus:bg-white transition-all">
                        </div>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-3.5 flex items-start gap-3">
                        <input type="checkbox" name="fixado" id="pFixado" value="1" class="w-4 h-4 mt-0.5 border-stone-300 rounded accent-stone-900 cursor-pointer">
                        <div>
                            <label for="pFixado" class="text-xs font-bold uppercase text-amber-900 cursor-pointer block">Fixar com Promoção</label>
                            <span class="text-[11px] text-amber-700 leading-tight block mt-0.5">Coloca o produto no topo da vitrine e adiciona efeitos e selos visuais de destaque.</span>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase text-stone-500 block mb-1">Imagem do Produto</label>
                        <input type="file" name="imagem" accept="image/*" class="w-full text-xs text-stone-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-stone-900 file:text-white hover:file:bg-black cursor-pointer">
                    </div>

                    <div class="pt-2 flex gap-2">
                        <button type="submit" class="flex-1 bg-stone-900 text-white py-3 rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-black transition-all">Salvar</button>
                        <button type="button" id="btnCancel" onclick="resetForm()" class="hidden bg-stone-200 text-stone-700 px-4 py-3 rounded-xl text-xs font-bold uppercase hover:bg-stone-300 transition-all">Cancelar</button>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white border border-stone-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-stone-100 border-b border-stone-200 text-[10px] font-black uppercase tracking-wider text-stone-500">
                                <th class="p-4">Item</th>
                                <th class="p-4">Preço</th>
                                <th class="p-4">Estoque</th>
                                <th class="p-4 text-center">Status</th>
                                <th class="p-4 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-sm">
                            <?php foreach ($list as $item): ?>
                                <tr class="<?php echo $item['fixado'] ? 'bg-amber-50/20' : ''; ?> hover:bg-stone-50 transition-all">
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-stone-100 rounded-lg overflow-hidden flex items-center justify-center text-stone-400 shrink-0 relative">
                                                <?php if($item['imagem']): ?>
                                                    <img src="../uploads/<?php echo $item['imagem']; ?>" class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <i data-lucide="utensils" class="w-4 h-4"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="font-bold text-stone-900 flex items-center gap-1.5">
                                                    <?php echo htmlspecialchars($item['nome']); ?>
                                                    <?php if($item['fixado']): ?>
                                                        <span class="bg-amber-100 text-amber-800 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wider flex items-center gap-0.5">
                                                            <i data-lucide="sparkles" class="w-2.5 h-2.5"></i> Promo
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-stone-400 text-xs line-clamp-1 max-w-[200px]"><?php echo htmlspecialchars($item['descricao'] ?? ''); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 font-medium">R$ <?php echo number_format((float)$item['preco'], 2, ',', '.'); ?></td>
                                    <td class="p-4 text-stone-600 font-semibold"><?php echo $item['estoque']; ?> un</td>
                                    <td class="p-4 text-center">
                                        <?php if($item['estoque'] > 0): ?>
                                            <span class="text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-medium">Ativo</span>
                                        <?php else: ?>
                                            <span class="text-xs bg-red-100 text-red-800 px-2 py-0.5 rounded-full font-medium">Esgotado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick='editProduct(<?php echo json_encode($item); ?>)' class="p-2 text-stone-500 hover:text-stone-900 hover:bg-stone-100 rounded-xl transition-all">
                                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                            </button>
                                            <form action="product.php" method="POST" onsubmit="return confirm('Tem certeza que deseja deletar este produto?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" class="p-2 text-stone-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
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
    
    <script>
        lucide.createIcons();
        function editProduct(item) {
            document.getElementById('formAction').value = 'update';
            document.getElementById('productId').value = item.id;
            document.getElementById('pName').value = item.nome;
            document.getElementById('pDesc').value = item.descricao;
            document.getElementById('pPrice').value = item.preco;
            document.getElementById('pStorage').value = item.estoque;
            document.getElementById('pFixado').checked = (item.fixado == 1);
            document.getElementById('formTitle').innerText = 'Editar Item #' + item.id;
            document.getElementById('btnCancel').classList.remove('hidden');
        }
        function resetForm() {
            document.getElementById('productForm').reset();
            document.getElementById('formAction').value = 'create';
            document.getElementById('productId').value = '';
            document.getElementById('pFixado').checked = false;
            document.getElementById('formTitle').innerText = 'Novo Registro';
            document.getElementById('btnCancel').classList.add('hidden');
        }
    </script>
</body>
</html>