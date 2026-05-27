<?php
// user/perfil.php
declare(strict_types=1);

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
        $mensagem = "Por favor, preencha os campos obrigatórios (Nome e E-mail).";
        $erro = true;
    } else {
        // Verifica se o e-mail já pertence a outro usuário
        $sql_email = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
        $stmt_email = mysqli_prepare($conexao, $sql_email);
        mysqli_stmt_bind_param($stmt_email, "si", $email, $usuario_id);
        mysqli_stmt_execute($stmt_email);
        mysqli_stmt_store_result($stmt_email);
        
        if (mysqli_stmt_num_rows($stmt_email) > 0) {
            $mensagem = "Este e-mail já está sendo utilizado por outra conta.";
            $erro = true;
            mysqli_stmt_close($stmt_email);
        } else {
            mysqli_stmt_close($stmt_email);

            // Monta a query dinamicamente baseando-se no preenchimento ou não da senha
            if (!empty($senha_nova)) {
                $hash = password_hash($senha_nova, PASSWORD_DEFAULT);
                $sql_update = "UPDATE usuarios SET nome = ?, email = ?, senha = ? WHERE id = ?";
                $stmt_update = mysqli_prepare($conexao, $sql_update);
                mysqli_stmt_bind_param($stmt_update, "sssi", $nome, $email, $hash, $usuario_id);
            } else {
                $sql_update = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
                $stmt_update = mysqli_prepare($conexao, $sql_update);
                mysqli_stmt_bind_param($stmt_update, "ssi", $nome, $email, $usuario_id);
            }

            if (mysqli_stmt_execute($stmt_update)) {
                $mensagem = "Informações atualizadas com sucesso!";
                // Atualiza as variáveis de sessão para refletir imediatamente no sistema
                $_SESSION['usuario'] = $nome;
            } else {
                $mensagem = "Erro ao atualizar as informações no banco de dados.";
                $erro = true;
            }
            mysqli_stmt_close($stmt_update);
        }
    }
}

// 2. Busca as informações atualizadas do usuário logado para exibir no formulário
$sql_user = "SELECT nome, email, perfil FROM usuarios WHERE id = ?";
$stmt_user = mysqli_prepare($conexao, $sql_user);
mysqli_stmt_bind_param($stmt_user, "i", $usuario_id);
mysqli_stmt_execute($stmt_user);
$resultado = mysqli_stmt_get_result($stmt_user);
$user_data = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt_user);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - L-Essense</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet\">\
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>body { font-family: 'Inter', sans-serif; } .font-serif { font-family: 'Instrument Serif', serif; }</style>
</head>
<body class="bg-stone-50 min-h-screen text-stone-900 antialiased pb-12">

    <div class="max-w-6xl mx-auto px-4 pt-6">
        <?php include __DIR__ . '/menu.php'; ?>

        <div class="max-w-2xl mx-auto bg-white border border-stone-200/60 rounded-3xl p-8 md:p-10 shadow-sm mt-6">
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-stone-100">
                <div>
                    <h1 class="text-3xl font-serif text-stone-950">Configurações do Perfil</h1>
                    <p class="text-xs text-stone-400 font-medium uppercase tracking-wider mt-1">Gerencie suas informações pessoais</p>
                </div>
                
                <div class="text-right">
                    <span class="text-[9px] font-black uppercase tracking-widest text-stone-400 block mb-1">Nível de Acesso</span>
                    <span class="px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest <?php echo $user_data['perfil'] === 'admin' ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-700'; ?>">
                        <?php echo $user_data['perfil'] === 'admin' ? 'Gestor / Admin' : 'Cliente'; ?>
                    </span>
                </div>
            </div>

            <?php if (!empty($mensagem)): ?>
                <div class="mb-6 p-4 rounded-2xl text-xs font-bold uppercase tracking-wide flex items-center gap-2 <?php echo $erro ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-100'; ?>">
                    <i data-lucide="<?php echo $erro ? 'alert-circle' : 'check-circle'; ?>" class="w-4 h-4"></i>
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                <div>
                    <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 mb-1.5 block">Nome Completo</label>
                    <div class="relative">
                        <i data-lucide="user" class="w-4 h-4 text-stone-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                        <input type="text" name="nome" value="<?php echo htmlspecialchars($user_data['nome']); ?>" required
                            class="w-full pl-11 pr-4 py-3.5 bg-stone-50 border border-stone-200/60 focus:border-stone-400 focus:bg-white rounded-xl transition-all text-sm outline-none font-medium">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 mb-1.5 block">Endereço de E-mail</label>
                    <div class="relative">
                        <i data-lucide="mail" class="w-4 h-4 text-stone-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required
                            class="w-full pl-11 pr-4 py-3.5 bg-stone-50 border border-stone-200/60 focus:border-stone-400 focus:bg-white rounded-xl transition-all text-sm outline-none font-medium">
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 block">Nova Senha</label>
                        <span class="text-[9px] text-stone-400 italic">(Deixe em branco para manter a atual)</span>
                    </div>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 text-stone-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                        <input type="password" name="senha" placeholder="••••••••"
                            class="w-full pl-11 pr-4 py-3.5 bg-stone-50 border border-stone-200/60 focus:border-stone-400 focus:bg-white rounded-xl transition-all text-sm outline-none font-medium">
                    </div>
                </div>

                <div class="pt-4 border-t border-stone-100 flex flex-col sm:flex-row gap-3">
                    <button type="submit"
                        class="flex-1 bg-stone-900 text-white font-black uppercase tracking-widest text-[10px] py-4 rounded-xl hover:bg-black transition-all shadow-sm active:scale-[0.98] flex items-center justify-center gap-2">
                        <i data-lucide="save" class="w-3.5 h-3.5"></i> Salvar Alterações
                    </button>
                    <a href="home.php"
                        class="sm:w-32 bg-stone-100 text-stone-600 font-black uppercase tracking-widest text-[10px] py-4 rounded-xl hover:bg-stone-200 hover:text-stone-900 transition-all text-center block">
                        Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php include __DIR__ . '/rodape.php'; ?>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>