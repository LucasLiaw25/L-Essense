<?php
// dashboard/client.php
declare(strict_types=1);

require_once __DIR__ . '/../auth/Conexao.php'; 
require_once __DIR__ . '/../auth/ClientClass.php';
require_once __DIR__ . '/../auth/verificarADM.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($action === 'create' && !empty($name) && !empty($email)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nome, email, senha, perfil) VALUES (?, ?, ?, 'cliente')";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hash);
        if (mysqli_stmt_execute($stmt)) $message = "Cliente cadastrado!";
        mysqli_stmt_close($stmt);
    } elseif ($action === 'update' && $id > 0) {
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET nome = ?, email = ?, senha = ? WHERE id = ?";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $hash, $id);
        } else {
            $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $id);
        }
        if (mysqli_stmt_execute($stmt)) $message = "Cliente atualizado!";
        mysqli_stmt_close($stmt);
    } elseif ($action === 'delete' && $id > 0) {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) $message = "Cliente removido!";
        mysqli_stmt_close($stmt);
    }
}

$sql = "SELECT id, nome, email FROM usuarios WHERE perfil = 'cliente' ORDER BY nome ASC";
$res = mysqli_query($conexao, $sql);
$clientes = mysqli_fetch_all($res, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Clientes - L-Essense</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Instrument Serif', serif; }
    </style>
</head>
<body class="bg-stone-50 text-stone-800 p-4 md:p-8 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <?php include '../user/menu.php'; ?>

        <?php if (!empty($message)): ?>
            <div class="mb-6 p-4 bg-stone-900 text-stone-100 rounded-2xl text-xs font-bold uppercase tracking-widest flex items-center gap-2 shadow-md">
                <i data-lucide="info" class="w-4 h-4 text-stone-400"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="bg-white border border-stone-200/80 rounded-3xl p-6 shadow-sm h-fit">
                <h2 id="formTitle" class="font-serif text-2xl text-stone-900 mb-6">Novo Registro</h2>
                
                <form id="clientForm" action="" method="POST" class="space-y-4">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="clientId" value="">
                    
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 mb-1 block">Nome Completo</label>
                        <input type="text" name="name" id="cName" required 
                               class="w-full h-11 px-4 bg-stone-50 border border-stone-200/60 focus:border-stone-400 focus:bg-white rounded-xl text-sm font-medium outline-none transition-all">
                    </div>
                    
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 mb-1 block">E-mail Corporativo</label>
                        <input type="email" name="email" id="cEmail" required 
                               class="w-full h-11 px-4 bg-stone-50 border border-stone-200/60 focus:border-stone-400 focus:bg-white rounded-xl text-sm font-medium outline-none transition-all">
                    </div>
                    
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 mb-1 block">
                            Senha de Acesso <span id="pwdLabel" class="text-[9px] lowercase text-stone-400">(obrigatório para novos)</span>
                        </label>
                        <input type="password" name="password" id="cPassword" 
                               class="w-full h-11 px-4 bg-stone-50 border border-stone-200/60 focus:border-stone-400 focus:bg-white rounded-xl text-sm font-medium outline-none transition-all">
                    </div>
                    
                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="flex-1 h-11 bg-stone-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-black transition-all active:scale-95 shadow-sm">
                            Salvar Registro
                        </button>
                        <button type="button" id="btnCancel" onclick="resetForm()" class="hidden h-11 px-4 border border-stone-200 text-stone-500 rounded-xl hover:bg-stone-50 transition-all text-xs font-bold active:scale-95">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white border border-stone-200/80 rounded-3xl overflow-hidden shadow-sm">
                <div class="p-6 border-b border-stone-100 flex justify-between items-center bg-stone-50/50">
                    <h2 class="font-serif text-2xl text-stone-900">Base de Clientes</h2>
                    <span class="text-[10px] font-black bg-stone-200 text-stone-700 px-2.5 py-1 rounded-full uppercase tracking-wider"><?php echo count($clientes); ?> Ativos</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-stone-100 text-[10px] font-black uppercase tracking-widest text-stone-400 bg-stone-50/30">
                                <th class="py-4 px-6">ID</th>
                                <th class="py-4 px-6">Nome</th>
                                <th class="py-4 px-6">E-mail</th>
                                <th class="py-4 px-6 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-sm font-medium text-stone-700">
                            <?php foreach ($clientes as $c): ?>
                                <tr class="hover:bg-stone-50/60 transition-colors duration-200">
                                    <td class="py-4 px-6 font-mono text-xs text-stone-400">#<?php echo $c['id']; ?></td>
                                    <td class="py-4 px-6 text-stone-900 font-semibold"><?php echo htmlspecialchars($c['nome']); ?></td>
                                    <td class="py-4 px-6 text-stone-500"><?php echo htmlspecialchars($c['email']); ?></td>
                                    <td class="py-4 px-6 text-right flex justify-end gap-1.5">
                                        <button onclick='editClient(<?php echo json_encode($c, JSON_HEX_APOS|JSON_HEX_QUOT); ?>)' class="h-8 w-8 rounded-lg border border-stone-200 flex items-center justify-center text-stone-500 hover:text-stone-900 hover:bg-stone-50 transition-all duration-200 active:scale-90">
                                            <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <form action="" method="POST" onsubmit="return confirm('Excluir cliente?');" class="inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                            <button type="submit" class="h-8 w-8 rounded-lg border border-stone-200 flex items-center justify-center text-stone-400 hover:text-white hover:bg-red-600 hover:border-red-600 transition-all duration-200 active:scale-90">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
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

        function editClient(client) {
            document.getElementById('formAction').value = 'update';
            document.getElementById('clientId').value = client.id;
            document.getElementById('cName').value = client.nome;
            document.getElementById('cEmail').value = client.email;
            document.getElementById('formTitle').innerText = 'Editar Cliente #' + client.id;
            document.getElementById('pwdLabel').innerText = '(deixe em branco para não alterar)';
            document.getElementById('btnCancel').classList.remove('hidden');
        }

        function resetForm() {
            document.getElementById('clientForm').reset();
            document.getElementById('formAction').value = 'create';
            document.getElementById('clientId').value = '';
            document.getElementById('formTitle').innerText = 'Novo Registro';
            document.getElementById('pwdLabel').innerText = '(obrigatório para novos)';
            document.getElementById('btnCancel').classList.add('hidden');
        }
    </script>
</body>
</html>