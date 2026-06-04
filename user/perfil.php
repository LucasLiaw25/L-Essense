<?php
// user/perfil.php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth/VerificarLogin.php';
require_once __DIR__ . '/../auth/Conexao.php';

global $conexao;

$usuario_id = (int)$_SESSION['usuario_id'];
$mensagem = "";
$erro = false;

// 1. Processa a atualização do formulário se houver uma requisição POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha_nova = $_POST['senha'] ?? '';

    if (empty($nome) || empty($email)) {
        $mensagem = "Por favor, preencha todos os campos obrigatórios (Nome e E-mail).";
        $erro = true;
    } else {
        // Verifica se o e-mail já pertence a outro usuário
        $sql_email = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
        $stmt_email = mysqli_prepare($conexao, $sql_email);
        mysqli_stmt_bind_param($stmt_email, "si", $email, $usuario_id);
        mysqli_stmt_execute($stmt_email);
        mysqli_stmt_store_result($stmt_email);
        
        if (mysqli_stmt_num_rows($stmt_email) > 0) {
            $mensagem = "Este e-mail já está a ser utilizado por outro utilizador.";
            $erro = true;
            mysqli_stmt_close($stmt_email);
        } else {
            mysqli_stmt_close($stmt_email);

            // Se digitou uma nova senha, atualiza com a senha criptografada
            if (!empty($senha_nova)) {
                $senha_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
                $sql_update = "UPDATE usuarios SET nome = ?, email = ?, senha = ? WHERE id = ?";
                $stmt_update = mysqli_prepare($conexao, $sql_update);
                mysqli_stmt_bind_param($stmt_update, "sssi", $nome, $email, $senha_hash, $usuario_id);
            } else {
                // Se não digitou senha, mantém a senha atual intacta
                $sql_update = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
                $stmt_update = mysqli_prepare($conexao, $sql_update);
                mysqli_stmt_bind_param($stmt_update, "ssi", $nome, $email, $usuario_id);
            }

            if (mysqli_stmt_execute($stmt_update)) {
                $mensagem = "Os seus dados foram atualizados com sucesso!";
                $_SESSION['usuario'] = $nome; // Atualiza o nome exibido no menu de navegação
            } else {
                $mensagem = "Erro crítico ao tentar atualizar os dados no banco de dados.";
                $erro = true;
            }
            mysqli_stmt_close($stmt_update);
        }
    }
}

// 2. Busca os dados mais recentes do utilizador para preencher o formulário inicialmente
$sql_user = "SELECT nome, email FROM usuarios WHERE id = ?";
$stmt_user = mysqli_prepare($conexao, $sql_user);
mysqli_stmt_bind_param($stmt_user, "i", $usuario_id);
mysqli_stmt_execute($stmt_user);
$resultado = mysqli_stmt_get_result($stmt_user);
$dados_usuario = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt_user);

// Fallback preventivo caso o registo não seja encontrado
$nome_atual = $dados_usuario['nome'] ?? $_SESSION['usuario'] ?? '';
$email_atual = $dados_usuario['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A minha Conta | L-Essense</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Instrument Serif', serif; }
    </style>
</head>
<body class="bg-stone-50 text-stone-900 antialiased min-h-screen p-4 md:p-8">

    <div class="max-w-4xl mx-auto space-y-8">
        <?php include __DIR__ . '/../user/menu.php'; ?>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-stone-200 pb-6">
            <div>
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-600 block mb-1">Configurações Gerais</span>
                <h1 class="text-3xl font-bold tracking-tight text-stone-950">Gerir Perfil</h1>
            </div>
            
            <?php if (!empty($mensagem)): ?>
                <div class="px-4 py-3 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm border transition-all animate-fade-in <?php echo $erro ? 'bg-red-50 border-red-200 text-red-900' : 'bg-emerald-50 border-emerald-200 text-emerald-900'; ?>">
                    <i data-lucide="<?php echo $erro ? 'alert-triangle' : 'check-circle'; ?>" class="w-4 h-4 shrink-0 <?php echo $erro ? 'text-red-600' : 'text-emerald-600'; ?>"></i>
                    <span><?php echo htmlspecialchars($mensagem); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white border border-stone-200/80 rounded-2xl shadow-sm max-w-2xl mx-auto overflow-hidden">
            <div class="p-6 bg-stone-50 border-b border-stone-100 flex items-center gap-3">
                <div class="w-10 h-10 bg-stone-950 text-white rounded-xl flex items-center justify-center font-serif italic text-xl font-bold">
                    <?php echo mb_substr(htmlspecialchars($nome_atual), 0, 1); ?>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-stone-950">Dados de Acesso</h2>
                    <p class="text-xs text-stone-400">Mantenha as suas credenciais e informações pessoais sempre atualizadas.</p>
                </div>
            </div>

            <form action="perfil.php" method="POST" class="p-6 space-y-6">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase text-stone-400 tracking-widest ml-1 block">Nome Completo</label>
                    <div class="relative">
                        <i data-lucide="user" class="w-4 h-4 text-stone-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                        <input type="text" name="nome" value="<?php echo htmlspecialchars($nome_atual); ?>" required placeholder="Seu nome"
                            class="w-full pl-11 pr-4 py-3.5 bg-stone-50 border border-stone-200/60 focus:border-stone-400 focus:bg-white rounded-xl transition-all text-sm outline-none font-medium text-stone-900">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase text-stone-400 tracking-widest ml-1 block">Endereço de E-mail</label>
                    <div class="relative">
                        <i data-lucide="mail" class="w-4 h-4 text-stone-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email_atual); ?>" required placeholder="exemplo@email.com"
                            class="w-full pl-11 pr-4 py-3.5 bg-stone-50 border border-stone-200/60 focus:border-stone-400 focus:bg-white rounded-xl transition-all text-sm outline-none font-medium text-stone-900">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <div class="flex justify-between items-center px-1">
                        <label class="text-[10px] font-black uppercase text-stone-400 tracking-widest block">Nova Senha de Segurança</label>
                        <span class="text-[9px] text-stone-400 font-medium lowercase">(deixe em branco para manter a atual)</span>
                    </div>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 text-stone-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                        <input type="password" name="senha" placeholder="••••••••"
                            class="w-full pl-11 pr-4 py-3.5 bg-stone-50 border border-stone-200/60 focus:border-stone-400 focus:bg-white rounded-xl transition-all text-sm outline-none font-medium text-stone-900">
                    </div>
                </div>

                <div class="pt-4 border-t border-stone-100 flex flex-col sm:flex-row gap-3">
                    <button type="submit"
                        class="flex-1 bg-stone-900 text-white font-black uppercase tracking-widest text-[10px] py-4 rounded-xl hover:bg-black transition-all shadow-sm active:scale-[0.98] flex items-center justify-center gap-2">
                        <i data-lucide="save" class="w-3.5 h-3.5"></i> Salvar Alterações
                    </button>
                    
                    <a href="home.php"
                        class="sm:w-32 bg-stone-100 text-stone-600 font-black uppercase tracking-widest text-[10px] py-4 rounded-xl hover:bg-stone-200 hover:text-stone-900 transition-all text-center flex items-center justify-center gap-1">
                        Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php include __DIR__ . '/../user/rodape.php'; ?>

    <script>
        // Inicializa dinamicamente todos os ícones estruturais do Lucide
        lucide.createIcons();
    </script>
</body>
</html>