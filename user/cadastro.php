<?php
ob_start();
session_start();

require_once __DIR__ . '/../auth/Conexao.php';

if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header("Location: home.php");
    exit();
}

$erro = "";
$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $senha_confirma = $_POST['senha_confirma'] ?? '';

    if (empty($nome) || empty($email) || empty($senha) || empty($senha_confirma)) {
        $erro = "Por favor, preencha todos os campos obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Informe um e-mail válido.";
    } elseif ($senha !== $senha_confirma) {
        $erro = "As senhas não coincidem.";
    } elseif (strlen($senha) < 6) {
        $erro = "A senha deve ter ao menos 6 caracteres.";
    } else {
        $sql_verifica = "SELECT id FROM usuarios WHERE email = ?";
        $stmt_verifica = mysqli_prepare($conexao, $sql_verifica);
        mysqli_stmt_bind_param($stmt_verifica, "s", $email);
        mysqli_stmt_execute($stmt_verifica);
        mysqli_stmt_store_result($stmt_verifica);

        if (mysqli_stmt_num_rows($stmt_verifica) > 0) {
            $erro = "Este e-mail já está registrado.";
            mysqli_stmt_close($stmt_verifica);
        } else {
            mysqli_stmt_close($stmt_verifica);

            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $perfil_padrao = 'cliente';

            $sql_insert = "INSERT INTO usuarios (nome, email, senha, perfil) VALUES (?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($conexao, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "ssss", $nome, $email, $senha_hash, $perfil_padrao);

            if (mysqli_stmt_execute($stmt_insert)) {
                $mensagem = "Conta criada com sucesso! Redirecionando...";
                header("Refresh: 2; url=login.php");
            } else {
                $erro = "Erro interno ao salvar os dados. Tente mais tarde.";
            }
            mysqli_stmt_close($stmt_insert);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro | L-Essense</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Instrument Serif', serif; }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }
        .animate-shake { animation: shake 0.4s ease-in-out; }
    </style>
</head>
<body class="bg-stone-50 text-stone-900 antialiased min-h-screen flex flex-col md:flex-row">

    <main class="w-full md:w-1/2 flex flex-col justify-between p-8 md:p-16 lg:p-24 min-h-screen bg-white">
        
        <div>
            <span class="font-serif text-2xl italic tracking-wide text-stone-950 block">L-Essense</span>
        </div>

        <div class="max-w-md w-full mx-auto my-auto space-y-6 py-8">
            <div class="space-y-2">
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-600 block">Criar Nova Conta</span>
                <h1 class="text-4xl font-bold tracking-tight text-stone-950">Junte-se a nós.</h1>
                <p class="text-xs text-stone-400 font-medium leading-relaxed">Faça parte da nossa boutique gastronômica e faça seus pedidos personalizados.</p>
            </div>

            <?php if (!empty($erro)): ?>
                <div class="animate-shake bg-red-50 border border-red-200 text-red-700 text-xs font-semibold px-4 py-3 rounded-xl flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 shrink-0"></i>
                    <span><?php echo htmlspecialchars($erro); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($mensagem)): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold px-4 py-3 rounded-xl flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                    <span><?php echo htmlspecialchars($mensagem); ?></span>
                </div>
            <?php endif; ?>

            <form action="cadastro.php" method="POST" class="space-y-4">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 block">Nome Completo</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-stone-400">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="nome" value="<?php echo htmlspecialchars($nome ?? ''); ?>" placeholder="Seu nome completo" required
                            class="w-full pl-11 pr-4 py-4 bg-stone-100/50 border border-stone-200/60 focus:border-amber-500 focus:bg-white rounded-2xl transition-all text-sm font-medium outline-none">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 block">Endereço de E-mail</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-stone-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </span>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" placeholder="exemplo@provedor.com" required
                            class="w-full pl-11 pr-4 py-4 bg-stone-100/50 border border-stone-200/60 focus:border-amber-500 focus:bg-white rounded-2xl transition-all text-sm font-medium outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 block">Senha</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-stone-400">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </span>
                            <input type="password" name="senha" placeholder="Mínimo 6 dígitos" required
                                class="w-full pl-11 pr-4 py-4 bg-stone-100/50 border border-stone-200/60 focus:border-amber-500 focus:bg-white rounded-2xl transition-all text-sm font-medium outline-none">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 block">Confirmar Senha</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-stone-400">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </span>
                            <input type="password" name="senha_confirma" placeholder="Repita a senha" required
                                class="w-full pl-11 pr-4 py-4 bg-stone-100/50 border border-stone-200/60 focus:border-amber-500 focus:bg-white rounded-2xl transition-all text-sm font-medium outline-none">
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-black uppercase tracking-[0.2em] text-[11px] py-5 rounded-2xl active:scale-95 transition-all shadow-lg shadow-amber-600/10 flex items-center justify-center gap-2">
                        Criar Conta <i data-lucide="user-plus" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

            <div class="text-center pt-2 text-[11px] text-stone-500 font-medium">
                Já possui uma conta ativa? <a href="login.php" class="font-black text-amber-600 hover:text-amber-700">Faça o login aqui</a>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-[9px] font-black uppercase tracking-[0.2em] text-stone-400">Ambiente Seguro & Monitorado</span>
        </div>

    </main>

    <aside class="hidden md:block w-1/2 bg-stone-950 relative overflow-hidden min-h-screen">
        <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&q=80&w=1200" 
             alt="Cozinha Profissional" 
             class="absolute inset-0 w-full h-full object-cover opacity-50 scale-105 transition-transform duration-700 hover:scale-100">
        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-transparent to-transparent"></div>
        
        <div class="absolute bottom-16 left-16 right-16 space-y-3">
            <p class="font-serif text-3xl italic text-stone-100 max-w-md leading-relaxed">
                "A paixão pelos detalhes transforma ingredientes simples em obras de arte."
            </p>
            <span class="text-[9px] font-black uppercase text-amber-500 tracking-wider block">— L-Essense Staff</span>
        </div>
    </aside>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>