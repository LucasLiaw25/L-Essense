<?php
// dashboard/product.php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ajustado para 'verificarADM.php' com "v" minúsculo para evitar erros em servidores Linux
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
    $desc = trim($_POST['desc'] ?? '');
    $price = (float)($_POST['price'] ?? 0.0);
    $storage = (int)($_POST['storage'] ?? 0);
    $fixado = isset($_POST['fixado']) ? 1 : 0;

    if ($action === 'create' && !empty($name)) {
        $img = handleImageUpload();
        $sql = "INSERT INTO produtos (nome, descricao, preco, estoque, imagem, fixado) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "ssdisi", $name, $desc, $price, $storage, $img, $fixado);
        if (mysqli_stmt_execute($stmt)) $message = "Produto cadastrado com sucesso!";
        mysqli_stmt_close($stmt);
    } elseif ($action === 'update' && $id > 0) {
        $img = handleImageUpload();
        if ($img) {
            $sql = "UPDATE produtos SET nome=?, descricao=?, preco=?, estoque=?, imagem=?, fixado=? WHERE id=?";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "ssdisii", $name, $desc, $price, $storage, $img, $fixado, $id);
        } else {
            $sql = "UPDATE produtos SET nome=?, descricao=?, preco=?, estoque=?, fixado=? WHERE id=?";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "ssdiii", $name, $desc, $price, $storage, $fixado, $id);
        }
        if (mysqli_stmt_execute($stmt)) $message = "Produto atualizado com sucesso!";
        mysqli_stmt_close($stmt);
    } elseif ($action === 'delete' && $id > 0) {
        $sql = "DELETE FROM produtos WHERE id = ?";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) $message = "Produto removido!";
        mysqli_stmt_close($stmt);
    }
    header("Location: product.php?msg=" . urlencode($message));
    exit();
}

$msgGet = $_GET['msg'] ?? '';
$sql = "SELECT * FROM produtos ORDER BY fixado DESC, nome ASC";
$resultado = mysqli_query($conexao, $sql);
$items = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Produtos | L-Essense</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Instrument Serif', serif; }
    </style>
</head>
<body class="bg-stone-50 text-stone-900 antialiased min-h-screen p-4 md:p-8">

    <div class="max-w-7xl mx-auto space-y-8">
        <?php include __DIR__ . '/../user/menu.php'; ?>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-stone-200 pb-6">
            <div>
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-600 block mb-1">Módulo de Gestão</span>
                <h1 class="text-3xl font-bold tracking-tight text-stone-950">Cardápio & Produtos</h1>
            </div>
            <?php if (!empty($msgGet)): ?>
                <div class="bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm">
                    <i data-lucide="info" class="w-4 h-4 text-amber-600"></i>
                    <span><?php echo htmlspecialchars($msgGet); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <div class="bg-white border border-stone-200/80 rounded-2xl p-6 shadow-sm sticky top-24">
                <div class="flex items-center justify-between mb-6">
                    <h2 id="formTitle" class="text-base font-bold tracking-tight text-stone-950">Novo Registro</h2>
                    <button id="btnCancel" onclick="resetForm()" class="hidden text-[10px] font-black uppercase tracking-wider text-stone-400 hover:text-red-500 transition-all">Cancelar</button>
                </div>

                <form id="productForm" action="product.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="productId" value="">

                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 block">Nome do Prato / Item</label>
                        <input type="text" name="name" id="pName" required placeholder="Ex: Risoto de Alho Poró"
                            class="w-full px-4 py-3 bg-stone-50 border border-stone-200 focus:border-amber-500 focus:bg-white rounded-xl transition-all text-sm font-medium outline-none">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 block">Descrição Detalhada</label>
                        <textarea name="desc" id="pDesc" rows="3" placeholder="Ingredientes, alérgenos ou apresentação..."
                            class="w-full px-4 py-3 bg-stone-50 border border-stone-200 focus:border-amber-500 focus:bg-white rounded-xl transition-all text-sm font-medium outline-none resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 block">Preço (R$)</label>
                            <input type="number" step="0.01" name="price" id="pPrice" required placeholder="0.00"
                                class="w-full px-4 py-3 bg-stone-50 border border-stone-200 focus:border-amber-500 focus:bg-white rounded-xl transition-all text-sm font-medium outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 block">Estoque Inicial</label>
                            <input type="number" name="storage" id="pStorage" required placeholder="0"
                                class="w-full px-4 py-3 bg-stone-50 border border-stone-200 focus:border-amber-500 focus:bg-white rounded-xl transition-all text-sm font-medium outline-none">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 block">Fotografia do Prato</label>
                        <input type="file" name="imagem" accept="image/*"
                            class="w-full text-xs text-stone-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-wider file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200 cursor-pointer">
                    </div>

                    <label class="flex items-center gap-3 bg-stone-50 border border-stone-200/60 p-3 rounded-xl cursor-pointer select-none hover:bg-stone-100/40 transition-all">
                        <input type="checkbox" name="fixado" id="pFixado" value="1" class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500 border-stone-300">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-stone-800">Destacar no Topo</span>
                            <span class="text-[9px] text-stone-400 font-medium font-semibold uppercase tracking-wider">Fixar este prato no início da listagem</span>
                        </div>
                    </label>

                    <button type="submit"
                        class="w-full bg-amber-600 hover:bg-amber-700 text-white font-black uppercase tracking-[0.15em] text-[10px] py-4 rounded-xl transition-all shadow-md shadow-amber-600/10 flex items-center justify-center gap-2 mt-2">
                        <i data-lucide="save" class="w-3.5 h-3.5"></i> Salvar Informações
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white border border-stone-200/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-stone-50/70 border-b border-stone-200 text-[10px] font-black uppercase tracking-widest text-stone-400">
                                <th class="p-4 pl-6">Item</th>
                                <th class="p-4">Preço</th>
                                <th class="p-4">Estoque</th>
                                <th class="p-4 text-right pr-6">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-sm font-medium text-stone-700">
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="4" class="p-12 text-center text-xs font-semibold text-stone-400">Nenhum produto cadastrado no momento.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($items as $item): ?>
                                <tr class="hover:bg-stone-50/40 transition-all">
                                    <td class="p-4 pl-6">
                                        <div class="flex items-center gap-4">
                                            <?php if (!empty($item['imagem'])): ?>
                                                <img src="../uploads/<?php echo htmlspecialchars($item['imagem']); ?>" class="w-12 h-12 object-cover rounded-xl border border-stone-200 shadow-sm shrink-0">
                                            <?php else: ?>
                                                <div class="w-12 h-12 bg-stone-100 border border-stone-200 rounded-xl flex items-center justify-center text-stone-400 shrink-0">
                                                    <i data-lucide="utensils" class="w-5 h-5"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="space-y-0.5">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-stone-950"><?php echo htmlspecialchars($item['nome']); ?></span>
                                                    <?php if ($item['fixado'] == 1): ?>
                                                        <span class="bg-amber-500/10 text-amber-700 border border-amber-500/20 rounded-md text-[8px] font-black uppercase px-1.5 py-0.5 tracking-wider flex items-center gap-0.5"><i data-lucide="pin" class="w-2 h-2"></i> Fixado</span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="text-xs text-stone-400 font-normal line-clamp-1 max-w-sm"><?php echo htmlspecialchars($item['descricao'] ?? ''); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="p-4 font-serif text-base text-stone-900 font-bold">
                                        R$ <?php echo number_format((float)$item['preco'], 2, ',', '.'); ?>
                                    </td>
                                    
                                    <td class="p-4">
                                        <?php if ($item['estoque'] <= 5): ?>
                                            <span class="bg-red-50 text-red-700 rounded-lg px-2 py-1 text-xs font-bold inline-block border border-red-100"><?php echo $item['estoque']; ?> un (Baixo)</span>
                                        <?php else: ?>
                                            <span class="bg-stone-100 text-stone-700 rounded-lg px-2 py-1 text-xs font-bold inline-block"><?php echo $item['estoque']; ?> un</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="p-4 text-right pr-6">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick='editProduct(<?php echo json_encode($item, JSON_HEX_APOS|JSON_HEX_QUOT); ?>)' 
                                                class="h-9 w-9 bg-stone-100 hover:bg-amber-600 hover:text-white rounded-xl flex items-center justify-center text-stone-600 transition-all active:scale-95" title="Editar">
                                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                            </button>
                                            
                                            <form action="product.php" method="POST" onsubmit="return confirm('Tem certeza que deseja apagar este prato?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" class="h-9 w-9 bg-stone-100 hover:bg-red-500 hover:text-white rounded-xl flex items-center justify-center text-stone-500 transition-all active:scale-95" title="Excluir">
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