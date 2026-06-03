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
        if (mysqli_stmt_execute($stmt)) $message = "Dados do cliente atualizados!";
        mysqli_stmt_close($stmt);
    } elseif ($action === 'delete' && $id > 0) {
        $sql = "DELETE FROM usuarios WHERE id = ? AND perfil = 'cliente'";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) $message = "Cliente removido do sistema!";
        mysqli_stmt_close($stmt);
    }
    header("Location: client.php?msg=" . urlencode($message));
    exit();
}

$msgGet = $_GET['msg'] ?? '';
$sql = "SELECT id, nome, email FROM usuarios WHERE perfil = 'cliente' ORDER BY nome ASC";
$resultado = mysqli_query($conexao, $sql);
$clients = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Clientes | L-Essense</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-stone-50 text-stone-900 antialiased min-h-screen p-4 md:p-8">

    <div class="max-w-7xl mx-auto space-y-8">
        <?php include __DIR__ . '/../user/menu.php'; ?>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-stone-200 pb-6">
            <div>
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-600 block mb-1">Módulo de Controle</span>
                <h1 class="text-3xl font-bold tracking-tight text-stone-950">Base de Clientes</h1>
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

                <form id="clientForm" action="client.php" method="POST" class="space-y-4">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="clientId" value="">

                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 block">Nome Completo</label>
                        <input type="text" name="name" id="cName" required placeholder="Ex: Gabriel Silva"
                            class="w-full px-4 py-3 bg-stone-50 border border-stone-200 focus:border-amber-500 focus:bg-white rounded-xl transition-all text-sm font-medium outline-none">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 block">E-mail Corporativo / Pessoal</label>
                        <input type="email" name="email" id="cEmail" required placeholder="exemplo@email.com"
                            class="w-full px-4 py-3 bg-stone-50 border border-stone-200 focus:border-amber-500 focus:bg-white rounded-xl transition-all text-sm font-medium outline-none">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 block">
                            Senha de Acesso <span id="pwdLabel" class="text-[9px] text-stone-400 lowercase font-semibold tracking-normal">(obrigatório para novos)</span>
                        </label>
                        <input type="password" name="password" id="cPassword" placeholder="••••••••"
                            class="w-full px-4 py-3 bg-stone-50 border border-stone-200 focus:border-amber-500 focus:bg-white rounded-xl transition-all text-sm font-medium outline-none">
                    </div>

                    <button type="submit"
                        class="w-full bg-amber-600 hover:bg-amber-700 text-white font-black uppercase tracking-[0.15em] text-[10px] py-4 rounded-xl transition-all shadow-md shadow-amber-600/10 flex items-center justify-center gap-2 mt-2">
                        <i data-lucide="user-plus" class="w-3.5 h-3.5"></i> Salvar Registro
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white border border-stone-200/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-stone-50/70 border-b border-stone-200 text-[10px] font-black uppercase tracking-widest text-stone-400">
                                <th class="p-4 pl-6">ID</th>
                                <th class="p-4">Nome do Cliente</th>
                                <th class="p-4">Canal de E-mail</th>
                                <th class="p-4 text-right pr-6">Gerenciar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-sm font-medium text-stone-700">
                            <?php if (empty($clients)): ?>
                                <tr>
                                    <td colspan="4" class="p-12 text-center text-xs font-semibold text-stone-400">Nenhum cliente cadastrado em nossa base.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($clients as $client): ?>
                                <tr class="hover:bg-stone-50/40 transition-all">
                                    <td class="p-4 pl-6 font-mono text-xs text-stone-400">#<?php echo $client['id']; ?></td>
                                    <td class="p-4 font-bold text-stone-950"><?php echo htmlspecialchars($client['nome']); ?></td>
                                    <td class="p-4 text-stone-500 font-normal"><?php echo htmlspecialchars($client['email']); ?></td>
                                    
                                    <td class="p-4 text-right pr-6">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick='editClient(<?php echo json_encode($client, JSON_HEX_APOS|JSON_HEX_QUOT); ?>)' 
                                                class="h-9 w-9 bg-stone-100 hover:bg-amber-600 hover:text-white rounded-xl flex items-center justify-center text-stone-600 transition-all active:scale-95" title="Editar Ficha">
                                                <i data-lucide="edit" class="w-4 h-4"></i>
                                            </button>
                                            
                                            <form action="client.php" method="POST" onsubmit="return confirm('Esta ação excluirá permanentemente a conta do cliente. Confirmar?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
                                                <button type="submit" class="h-9 w-9 bg-stone-100 hover:bg-red-500 hover:text-white rounded-xl flex items-center justify-center text-stone-500 transition-all active:scale-95" title="Remover Cadastro">
                                                    <i data-lucide="user-x" class="w-4 h-4"></i>
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