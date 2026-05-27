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
            $sql = "UPDATE usuarios SET nome = ?, email = ?, senha = ? WHERE id = ? AND perfil = 'cliente'";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $hash, $id);
        } else {
            $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ? AND perfil = 'cliente'";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $id);
        }
        if (mysqli_stmt_execute($stmt)) $message = "Dados atualizados!";
        mysqli_stmt_close($stmt);
    } elseif ($action === 'delete' && $id > 0) {
        $sql = "DELETE FROM usuarios WHERE id = ? AND perfil = 'cliente'";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) $message = "Cliente removido!";
        mysqli_stmt_close($stmt);
    }
}

$sql = "SELECT id, nome, email FROM usuarios WHERE perfil = 'cliente' ORDER BY id DESC";
$res = mysqli_query($conexao, $sql);
$clients = mysqli_fetch_all($res, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Clientes - L-Essense</title>
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
            <h1 class="font-serif text-4xl italic text-stone-950">Controle de Clientes</h1>
        </div>

        <?php if (!empty($message)): ?>
            <div class="p-4 bg-stone-900 text-white text-xs font-bold rounded-2xl mb-6 shadow-md">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-2xl border border-stone-200/60 shadow-sm h-fit">
                <h2 id="formTitle" class="text-xs font-black uppercase tracking-widest text-stone-400 border-b pb-3 mb-4">Novo Registro</h2>
                <form id="clientForm" action="" method="POST" class="space-y-4">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="clientId" value="">

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-stone-400 mb-2">Nome do Cliente</label>
                        <input type="text" name="name" id="cName" required class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 text-sm font-medium rounded-xl outline-none focus:border-stone-400 transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-stone-400 mb-2">E-mail</label>
                        <input type="email" name="email" id="cEmail" required class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 text-sm font-medium rounded-xl outline-none focus:border-stone-400 transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-stone-400 mb-2">Senha <span id="pwdLabel" class="text-[9px] text-stone-400 lowercase italic">(obrigatório para novos)</span></label>
                        <input type="password" name="password" id="cPassword" class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 text-sm font-medium rounded-xl outline-none focus:border-stone-400 transition-all">
                    </div>

                    <button type="submit" class="w-full py-3 bg-stone-900 text-white font-black uppercase tracking-widest text-[10px] rounded-xl hover:bg-black transition-all">
                        Salvar Cliente
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
                                <th class="p-4">ID</th>
                                <th class="p-4">Nome</th>
                                <th class="p-4">E-mail</th>
                                <th class="p-4 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 font-medium text-stone-700">
                            <?php foreach ($clients as $c): ?>
                                <tr class="hover:bg-stone-50/60 transition-all">
                                    <td class="p-4 font-mono text-stone-400">#<?php echo $c['id']; ?></td>
                                    <td class="p-4 font-bold text-stone-900"><?php echo htmlspecialchars($c['nome']); ?></td>
                                    <td class="p-4 text-stone-500"><?php echo htmlspecialchars($c['email']); ?></td>
                                    <td class="p-4 text-right space-x-1">
                                        <button onclick="editClient(<?php echo htmlspecialchars(json_encode($c)); ?>)" class="p-2 bg-stone-100 text-stone-700 rounded-lg hover:bg-stone-200 transition-all"><i data-lucide="edit-2" class="w-3.5 h-3.5"></i></button>
                                        <form action="" method="POST" class="inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                            <button type="submit" onclick="return confirm('Deseja banir/deletar este cliente?')" class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-all"><i data-lucide="user-x" class="w-3.5 h-3.5"></i></button>
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