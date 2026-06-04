<?php
// dashboard/client.php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth/Conexao.php'; 
require_once __DIR__ . '/../auth/ClientClass.php';
require_once __DIR__ . '/../auth/verificarADM.php';

global $conexao;

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $perfil = trim($_POST['perfil'] ?? 'cliente'); // Captura o perfil escolhido (cliente ou admin)

    // Validação preventiva para garantir que não enviem lixo para o banco
    if ($perfil !== 'admin' && $perfil !== 'cliente') {
        $perfil = 'cliente';
    }

    if ($action === 'create' && !empty($name) && !empty($email)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        // MODIFICADO: Agora insere a variável $perfil em vez do texto fixo 'cliente'
        $sql = "INSERT INTO usuarios (nome, email, senha, perfil) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hash, $perfil);
        if (mysqli_stmt_execute($stmt)) {
            $message = $perfil === 'admin' ? "Administrador cadastrado com sucesso!" : "Cliente cadastrado com sucesso!";
        }
        mysqli_stmt_close($stmt);
    } elseif ($action === 'update' && $id > 0) {
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            // MODIFICADO: Atualiza também o perfil no UPDATE
            $sql = "UPDATE usuarios SET nome = ?, email = ?, senha = ?, perfil = ? WHERE id = ?";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "ssssi", $name, $email, $hash, $perfil, $id);
        } else {
            // MODIFICADO: Atualiza o perfil mesmo se a senha ficar em branco
            $sql = "UPDATE usuarios SET nome = ?, email = ?, perfil = ? WHERE id = ?";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $perfil, $id);
        }
        if (mysqli_stmt_execute($stmt)) {
            $message = "Registro atualizado com sucesso!";
        }
        mysqli_stmt_close($stmt);
    } elseif ($action === 'delete' && $id > 0) {
        // Impede que o próprio administrador logado se delete por acidente
        if ($id === (int)$_SESSION['usuario_id']) {
            $message = "Erro: Você não pode excluir a sua própria conta ativa!";
        } else {
            $sql = "DELETE FROM usuarios WHERE id = ?";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                $message = "Usuário removido com sucesso!";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Busca todos os usuários ordenando para listar administradores primeiro
$sql_list = "SELECT id, nome, email, perfil FROM usuarios ORDER BY perfil ASC, nome ASC";
$result = mysqli_query($conexao, $sql_list);
$usuarios = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Controle | L-Essense</title>
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
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-600 block mb-1">Gestão Avançada</span>
                <h1 class="text-3xl font-bold tracking-tight text-stone-950">Controle de Usuários</h1>
            </div>

            <?php if (!empty($message)): ?>
                <div class="px-4 py-3 rounded-xl text-xs font-semibold bg-stone-900 text-white flex items-center gap-2 shadow-sm border border-stone-800 transition-all">
                    <i data-lucide="info" class="w-4 h-4 text-amber-400"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <div class="bg-white border border-stone-200 rounded-2xl shadow-sm overflow-hidden sticky top-28">
                <div class="p-5 bg-stone-50 border-b border-stone-100 flex justify-between items-center">
                    <h2 id="formTitle" class="text-xs font-black uppercase tracking-wider text-stone-900">Novo Registro</h2>
                    <button id="btnCancel" onclick="resetForm()" class="text-[10px] font-bold text-red-500 uppercase tracking-wider hidden hover:underline">Cancelar</button>
                </div>

                <form id="clientForm" action="client.php" method="POST" class="p-5 space-y-4">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="clientId" value="">

                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-stone-400 tracking-wider block ml-1">Nome Completo</label>
                        <input type="text" name="name" id="cName" required placeholder="Ex: João Silva"
                            class="w-full px-4 py-3 bg-stone-50 border border-stone-200 focus:border-stone-400 focus:bg-white rounded-xl text-xs font-medium outline-none transition-all">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-stone-400 tracking-wider block ml-1">Endereço de E-mail</label>
                        <input type="email" name="email" id="cEmail" required placeholder="nome@provedor.com"
                            class="w-full px-4 py-3 bg-stone-50 border border-stone-200 focus:border-stone-400 focus:bg-white rounded-xl text-xs font-medium outline-none transition-all">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-stone-400 tracking-wider block ml-1">Nível de Acesso (Cargo)</label>
                        <select name="perfil" id="cPerfil" class="w-full px-4 py-3 bg-stone-50 border border-stone-200 focus:border-stone-400 focus:bg-white rounded-xl text-xs font-medium outline-none transition-all cursor-pointer">
                            <option value="cliente">Cliente (Acesso apenas ao Menu/Pedidos)</option>
                            <option value="admin">Administrador (Acesso Total ao Painel)</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <div class="flex justify-between items-center px-1">
                            <label class="text-[9px] font-black uppercase text-stone-400 tracking-wider block">Senha de Acesso</label>
                            <span id="pwdLabel" class="text-[8px] text-stone-400 lowercase">(obrigatório para novos)</span>
                        </div>
                        <input type="password" name="password" id="cPassword" placeholder="••••••••"
                            class="w-full px-4 py-3 bg-stone-50 border border-stone-200 focus:border-stone-400 focus:bg-white rounded-xl text-xs font-medium outline-none transition-all">
                    </div>

                    <button type="submit" class="w-full bg-stone-900 text-white font-black uppercase tracking-widest text-[10px] py-3.5 rounded-xl hover:bg-black transition-all shadow-sm flex items-center justify-center gap-2">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i> Confirmar Dados
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white border border-stone-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 bg-stone-50 border-b border-stone-100">
                    <h2 class="text-xs font-black uppercase tracking-wider text-stone-900">Usuários Registrados</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-stone-100 text-[9px] font-black uppercase tracking-wider text-stone-400 bg-stone-50/50">
                                <th class="py-3 px-4 w-12">ID</th>
                                <th class="py-3 px-4">Usuário / E-mail</th>
                                <th class="py-3 px-4 w-32">Cargo</th>
                                <th class="py-3 px-4 text-right w-24">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-xs font-medium text-stone-700">
                            <?php foreach ($usuarios as $u): ?>
                                <tr class="hover:bg-stone-50/50 transition-colors">
                                    <td class="py-3.5 px-4 font-mono text-stone-400 text-[11px]">#<?php echo $u['id']; ?></td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-stone-950"><?php echo htmlspecialchars($u['nome']); ?></div>
                                        <div class="text-[10px] text-stone-400 font-normal"><?php echo htmlspecialchars($u['email']); ?></div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <?php if ($u['perfil'] === 'admin'): ?>
                                            <span class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-md text-[10px] font-bold uppercase tracking-wider">Gestor ADM</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 bg-stone-100 text-stone-600 rounded-md text-[10px] font-medium">Cliente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick='editClient(<?php echo json_encode($u); ?>)' 
                                                class="w-8 h-8 rounded-lg border border-stone-200 flex items-center justify-center text-stone-500 hover:text-stone-900 hover:bg-stone-50 transition-all" title="Editar">
                                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                            </button>

                                            <form action="client.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este registro permanente?');" class="inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" class="w-8 h-8 rounded-lg border border-red-100 bg-red-50/30 flex items-center justify-center text-red-500 hover:text-red-700 hover:bg-red-50 transition-all" title="Excluir">
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

        function editClient(client) {
            document.getElementById('formAction').value = 'update';
            document.getElementById('clientId').value = client.id;
            document.getElementById('cName').value = client.nome;
            document.getElementById('cEmail').value = client.email;
            document.getElementById('cPerfil').value = client.perfil; // Preenche o select com o perfil correto do usuário
            document.getElementById('formTitle').innerText = 'Editar Usuário #' + client.id;
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