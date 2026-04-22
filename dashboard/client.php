<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
class Client {
    public int $id;
    public function __construct(
        public string $name,
        public string $email,
        public string $password
    ) {}
}

if (!isset($_SESSION['listClients'])) {
    $_SESSION['listClients'] = [];
}

$message = "";

function addClient(Client $client) {
    if (!isset($_SESSION['listClients'])) {
        $_SESSION['listClients'] = [];
    }

    foreach ($_SESSION['listClients'] as $findClient) {
        if ($findClient->email === $client->email) {
            return "Erro: Cliente com mesmo e-mail já cadastrado!";
        }
    }

    $lastClient = end($_SESSION['listClients']);
    $client->id = ($lastClient === false) ? 1 : $lastClient->id + 1;
    
    $_SESSION['listClients'][] = $client;
    
    return true;
}

function updateClient(Client $clientRequest, int $id) {
    $found = false;
    foreach ($_SESSION['listClients'] as $client) {
        if ($client->id == $id) {
            if (!empty($clientRequest->name)) $client->name = $clientRequest->name;
            if (!empty($clientRequest->email)) $client->email = $clientRequest->email;
            if (!empty($clientRequest->password)) $client->password = $clientRequest->password;
            $found = true;
            break;
        }
    }
    return $found;
}

function deleteClient(int $id) {
    $found = false;
    foreach ($_SESSION['listClients'] as $index => $client) {
        if ($client->id == $id) {
            $found = true;
            unset($_SESSION['listClients'][$index]);
            $_SESSION['listClients'] = array_values($_SESSION['listClients']);
            break;
        }
    }
    if ($found) {
        return "<div class='bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl mb-6 flex items-center gap-2'><i data-lucide='trash-2' class='w-4 h-4'></i> Cliente removido com sucesso</div>";
    } else {
        return "<div class='bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6'>Cliente não encontrado</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'create') {
        if (empty($_POST['client_name']) || empty($_POST['client_email']) || empty($_POST['client_password'])) {
            $message = "<div class='bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6'>Erro: Preencha todos os campos!</div>";
        } else {
            $client = new Client($_POST['client_name'], $_POST['client_email'], $_POST['client_password']);
            addClient($client);
            $message = "<div class='bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl mb-6'>Cliente cadastrado com sucesso!</div>";
        }
    } elseif ($action === 'update') {
        $id = (int) ($_POST['client_id'] ?? 0);
        $clientReq = new Client(
            $_POST['client_name'] ?? '',
            $_POST['client_email'] ?? '',
            $_POST['client_password'] ?? ''
        );
        if (updateClient($clientReq, $id)) {
            $message = "<div class='bg-sky-50 border border-sky-200 text-sky-800 px-4 py-3 rounded-xl mb-6'>Cliente #$id atualizado com sucesso!</div>";
        } else {
            $message = "<div class='bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6'>Erro: Cliente #$id não encontrado!</div>";
        }
    } elseif ($action === "delete") {
        $id = (int) $_POST['client_id'];
        $message = deleteClient($id);
    }
}

$totalClients = count($_SESSION['listClients']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L'Essence | Gestão de Clientes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif&family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #fafaf9; }
        .font-serif { font-family: 'Instrument Serif', serif; }
        .btn-delete:hover { color: #ef4444; background-color: #fef2f2; }
        input[type="number"]::-webkit-inner-spin-button, input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    </style>
</head>
<body class="text-stone-900 antialiased">
<div class="max-w-6xl mx-auto px-6">
    <?php require '../user/menu.php'; ?>
</div>
<div class="max-w-6xl mx-auto px-4 py-12">
    
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
        <div>
            <h1 class="text-5xl font-serif font-bold tracking-tight text-stone-900">Gestão de Clientes</h1>
            <p class="text-stone-500 mt-2 font-medium">Administre sua base de membros com sofisticação.</p>
        </div>
        <div class="flex items-center gap-2 text-stone-400 bg-white border border-stone-100 px-4 py-2 rounded-2xl shadow-sm">
            <i data-lucide="users" class="w-4 h-4"></i>
            <span class="text-xs font-bold uppercase tracking-widest">Painel Administrativo</span>
        </div>
    </header>

    <?= $message ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-12">
        <div class="bg-white border border-stone-200 p-6 rounded-[2rem] shadow-sm flex items-center gap-6">
            <div class="w-14 h-14 bg-stone-50 rounded-2xl flex items-center justify-center border border-stone-100">
                <i data-lucide="user-plus" class="w-7 h-7 text-stone-600"></i>
            </div>
            <div>
                <p class="text-4xl font-black text-stone-900"><?= $totalClients ?></p>
                <p class="text-[10px] font-black uppercase tracking-[0.15em] text-stone-400 mt-1">Total de Clientes</p>
            </div>
        </div>
        <div class="bg-white border border-stone-200 p-6 rounded-[2rem] shadow-sm flex items-center gap-6">
            <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center border border-emerald-100">
                <i data-lucide="shield-check" class="w-7 h-7 text-emerald-600"></i>
            </div>
            <div>
                <p class="text-4xl font-black text-emerald-900"><?= $totalClients > 0 ? 'Ativo' : 'Pendente' ?></p>
                <p class="text-[10px] font-black uppercase tracking-[0.15em] text-emerald-500 mt-1">Status do Banco</p>
            </div>
        </div>
        <div class="bg-white border border-stone-200 p-6 rounded-[2rem] shadow-sm flex items-center gap-6">
            <div class="w-14 h-14 bg-stone-900 rounded-2xl flex items-center justify-center">
                <i data-lucide="clock" class="w-7 h-7 text-stone-50"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-stone-900"><?= date('H:i') ?></p>
                <p class="text-[10px] font-black uppercase tracking-[0.15em] text-stone-400 mt-1">Última Sincronização</p>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-[380px_1fr] gap-10 items-start">
        
        <aside class="sticky top-8">
            <div class="bg-white border border-stone-200 rounded-[2.5rem] p-8 shadow-sm">
                <h2 id="formTitle" class="text-2xl font-serif font-bold mb-8 text-stone-800">Novo Cliente</h2>
                
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="action" id="formAction" value="create">
                    
                    <div id="idField" class="group hidden">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400 mb-2 block px-1">ID do Cliente</label>
                        <input type="number" name="client_id" placeholder="Ex: 1" class="w-full bg-amber-50/50 border-amber-100 rounded-2xl p-4 text-sm focus:ring-4 focus:ring-amber-50 outline-none transition-all border group-hover:border-amber-200 font-bold">
                    </div>

                    <div class="group">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400 mb-2 block px-1">Nome Completo</label>
                        <input type="text" name="client_name" placeholder="Ex: Lucas Liaw" class="w-full bg-stone-50 border-stone-200 rounded-2xl p-4 text-sm focus:ring-4 focus:ring-stone-100 outline-none transition-all border group-hover:border-stone-300">
                    </div>

                    <div class="group">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400 mb-2 block px-1">E-mail Institucional</label>
                        <input type="email" name="client_email" placeholder="email@exemplo.com" class="w-full bg-stone-50 border-stone-200 rounded-2xl p-4 text-sm focus:ring-4 focus:ring-stone-100 outline-none transition-all border group-hover:border-stone-300">
                    </div>

                    <div class="group">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400 mb-2 block px-1">Senha de Acesso</label>
                        <div class="relative">
                            <input type="password" name="client_password" placeholder="••••••••" class="w-full bg-stone-50 border-stone-200 rounded-2xl p-4 text-sm focus:ring-4 focus:ring-stone-100 outline-none transition-all border group-hover:border-stone-300">
                            <i data-lucide="lock" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-300"></i>
                        </div>
                    </div>

                    <div class="pt-6 space-y-3">
                        <button type="submit" id="mainSubmit" class="w-full bg-stone-900 hover:bg-black text-stone-50 font-bold py-5 rounded-2xl transition-all flex items-center justify-center gap-3 shadow-lg shadow-stone-200">
                            <i data-lucide="user-plus" class="w-5 h-5"></i> <span id="btnText">Cadastrar Cliente</span>
                        </button>
                        
                        <button type="button" id="toggleMode" class="w-full bg-white border border-stone-200 hover:bg-stone-50 text-stone-600 font-bold py-4 rounded-2xl transition-all text-sm">
                            Atualizar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </aside>

        <main class="space-y-6">
            <div class="bg-white border border-stone-200 rounded-[2.5rem] shadow-sm overflow-hidden">
                <div class="p-8 border-b border-stone-100 flex justify-between items-center">
                    <h2 class="text-2xl font-serif font-bold text-stone-800">Membros Registrados</h2>
                    <span class="text-[10px] font-black text-stone-400 uppercase tracking-widest bg-stone-50 px-3 py-1 rounded-full border border-stone-100"><?= $totalClients ?> Clientes</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-stone-50/50 text-[10px] font-black uppercase tracking-[0.2em] text-stone-400">
                                <th class="px-8 py-5">ID & Nome</th>
                                <th class="px-8 py-5">Contato</th>
                                <th class="px-8 py-5">Segurança</th>
                                <th class="px-8 py-5 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            <?php foreach ($_SESSION['listClients'] as $c): ?>
                            <tr class="group hover:bg-stone-50/30 transition-colors">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-stone-100 rounded-full flex items-center justify-center text-stone-500 font-bold text-xs">
                                            <?= strtoupper(substr($c->name, 0, 1)) ?>
                                        </div>
                                        <div>
                                            <p class="font-bold text-stone-900">#<?= $c->id ?> - <?= htmlspecialchars($c->name) ?></p>
                                            <p class="text-[10px] text-stone-400 uppercase tracking-tighter">Membro Premium</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm text-stone-600 font-medium"><?= htmlspecialchars($c->email) ?></p>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-2">
                                        <div class="flex gap-0.5">
                                            <?php for($i=0; $i<5; $i++): ?>
                                                <span class="w-1 h-1 rounded-full bg-stone-200"></span>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="text-[10px] font-bold text-stone-300 uppercase">Protegida</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <form method="POST" onsubmit="return confirm('Excluir este cliente permanentemente?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="client_id" value="<?= $c->id ?>">
                                        <button type="submit" class="p-3 text-stone-400 btn-delete rounded-xl transition-all">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>

                            <?php if (empty($_SESSION['listClients'])): ?>
                            <tr>
                                <td colspan="4" class="px-8 py-24 text-center">
                                    <div class="flex flex-col items-center gap-4 text-stone-300">
                                        <div class="w-16 h-16 bg-stone-50 rounded-full flex items-center justify-center">
                                            <i data-lucide="user-x" class="w-8 h-8"></i>
                                        </div>
                                        <p class="font-serif text-xl italic text-stone-400">Nenhum cliente registrado ainda.</p>
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
    // Inicializar Ícones
    lucide.createIcons();

    // Lógica de Alternância do Formulário (Update vs Create)
    const toggleBtn = document.getElementById('toggleMode');
    const formAction = document.getElementById('formAction');
    const formTitle = document.getElementById('formTitle');
    const idField = document.getElementById('idField');
    const btnText = document.getElementById('btnText');
    const mainSubmit = document.getElementById('mainSubmit');

    let isUpdateMode = false;

    toggleBtn.addEventListener('click', () => {
        isUpdateMode = !isUpdateMode;

        if (isUpdateMode) {
            // Mudar para modo Atualização
            formAction.value = 'update';
            formTitle.innerText = 'Atualizar Cliente';
            btnText.innerText = 'Salvar Alterações';
            toggleBtn.innerText = 'Novo Cadastro';
            idField.classList.remove('hidden');
            mainSubmit.classList.replace('bg-stone-900', 'bg-sky-700');
            mainSubmit.classList.replace('hover:bg-black', 'hover:bg-sky-800');
        } else {
            // Mudar para modo Criação
            formAction.value = 'create';
            formTitle.innerText = 'Novo Registro';
            btnText.innerText = 'Cadastrar Cliente';
            toggleBtn.innerText = 'Atualizar Cliente';
            idField.classList.add('hidden');
            mainSubmit.classList.replace('bg-sky-700', 'bg-stone-900');
            mainSubmit.classList.replace('hover:bg-sky-800', 'hover:bg-black');
        }
    });
</script>
<?php include '../user/rodape.php' ?>
</body>
</html>