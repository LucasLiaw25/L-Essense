<?php
// dashboard/product.php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require '../auth/VerificarADM.php';
require '../auth/Conexao.php';

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
    $price = (float)($_POST['price'] ?? 0.0);
    $storage = (int)($_POST['storage'] ?? 0);

    if ($action === 'create' && !empty($name)) {
        $img = handleImageUpload();
        $sql = "INSERT INTO produtos (nome, descricao, preco, estoque, imagem) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "ssdis", $name, $description, $price, $storage, $img);
        if (mysqli_stmt_execute($stmt)) $message = "Produto criado com sucesso!";
        mysqli_stmt_close($stmt);
    } elseif ($action === 'update' && $id > 0 && !empty($name)) {
        $img = handleImageUpload();
        if ($img) {
            $sql = "UPDATE produtos SET nome = ?, descricao = ?, preco = ?, estoque = ?, imagem = ? WHERE id = ?";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "ssdisi", $name, $description, $price, $storage, $img, $id);
        } else {
            $sql = "UPDATE produtos SET nome = ?, descricao = ?, preco = ?, estoque = ? WHERE id = ?";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "ssdii", $name, $description, $price, $storage, $id);
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
}

$sql = "SELECT * FROM produtos ORDER BY id DESC";
$res = mysqli_query($conexao, $sql);
$catalog = mysqli_fetch_all($res, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Produtos - L-Essense</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>body { font-family: 'Inter', sans-serif; } .font-serif { font-family: 'Instrument Serif', serif; }</style>
</head>
<body class="bg-stone-50 text-stone-900 min-h-screen p-4 md:p-8">
    <div class="max-w-6xl mx-auto">
        <?php include '../user/menu.php'; ?>

        <div class="mb-8 border-b border-stone-200 pb-3">
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400 block mb-1">Módulo Administrativo</span>
            <h1 class="font-serif text-4xl italic text-stone-950">Gerenciamento de Produtos</h1>
        </div>

        <?php if (!empty($message)): ?>
            <div class="p-4 bg-stone-900 text-white text-xs font-bold rounded-2xl mb-6 shadow-md">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-2xl border border-stone-200/60 shadow-sm h-fit">
                <h2 id="formTitle" class="text-xs font-black uppercase tracking-widest text-stone-400 border-b pb-3 mb-4">Novo Registro</h2>
                <form id="productForm" action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="productId" value="">

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-stone-400 mb-2">Nome do Item</label>
                        <input type="text" name="name" id="pName" required class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 text-sm font-medium rounded-xl outline-none focus:border-stone-400 transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-stone-400 mb-2">Descrição Curta</label>
                        <textarea name="description" id="pDesc" rows="3" class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 text-sm font-medium rounded-xl outline-none focus:border-stone-400 transition-all"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-stone-400 mb-2">Preço (R$)</label>
                            <input type="number" step="0.01" name="price" id="pPrice" required class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 text-sm font-medium rounded-xl outline-none focus:border-stone-400 transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-stone-400 mb-2">Estoque</label>
                            <input type="number" name="storage" id="pStorage" required class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 text-sm font-medium rounded-xl outline-none focus:border-stone-400 transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-stone-400 mb-2">Foto do Produto</label>
                        <input type="file" name="imagem" accept="image/*" class="w-full text-xs text-stone-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200 file:transition-all">
                    </div>

                    <button type="submit" id="btnSubmit" class="w-full py-3 bg-stone-900 text-white font-black uppercase tracking-widest text-[10px] rounded-xl hover:bg-black transition-all">
                        Salvar Produto
                    </button>
                    <button type="button" id="btnCancel" onclick="resetForm()" class="w-full py-2 bg-stone-100 text-stone-600 font-black uppercase tracking-widest text-[9px] rounded-xl hover:bg-stone-200 transition-all hidden">
                        Cancelar Edição
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white rounded-2xl border border-stone-200/60 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-stone-50 text-stone-400 border-b border-stone-100 font-black uppercase tracking-wider text-[9px]">
                                <th class="p-4">Item</th>
                                <th class="p-4">Preço</th>
                                <th class="p-4">Estoque</th>
                                <th class="p-4 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 font-medium text-stone-700">
                            <?php foreach ($catalog as $item): ?>
                                <tr class="hover:bg-stone-50/60 transition-all">
                                    <td class="p-4 flex items-center gap-3">
                                        <?php if (!empty($item['imagem'])): ?>
                                            <img src="../uploads/<?php echo $item['imagem']; ?>" class="w-10 h-10 object-cover rounded-lg border border-stone-100">
                                        <?php else: ?>
                                            <div class="w-10 h-10 bg-stone-100 rounded-lg flex items-center justify-center text-stone-400"><i data-lucide="box" class="w-4 h-4"></i></div>
                                        <?php endif; ?>
                                        <div>
                                            <span class="font-bold text-stone-900 block"><?php echo htmlspecialchars($item['nome']); ?></span>
                                            <span class="text-[10px] text-stone-400 line-clamp-1"><?php echo htmlspecialchars($item['descricao'] ?? ''); ?></span>
                                        </div>
                                    </td>
                                    <td class="p-4 font-mono">R$ <?php echo number_format((float)$item['preco'], 2, ',', '.'); ?></td>
                                    <td class="p-4"><?php echo $item['estoque']; ?> un</td>
                                    <td class="p-4 text-right space-x-1 whitespace-nowrap">
                                        <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($item)); ?>)" class="p-2 bg-stone-100 text-stone-700 rounded-lg hover:bg-stone-200 transition-all"><i data-lucide="edit-2" class="w-3.5 h-3.5"></i></button>
                                        <form action="" method="POST" class="inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" onclick="return confirm('Deseja deletar este item?')" class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-all"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                                        </form>
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