<?php
ob_start();
session_start();

// Importa a conexão com o banco de dados
require_once __DIR__ . '/../auth/Conexao.php'; 

// Se já estiver logado, vai direto para a home
if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header("Location: home.php");
    exit();
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_digitado = trim($_POST['email'] ?? '');
    $senha_digitada = $_POST['senha'] ?? '';

    if (empty($email_digitado) || empty($senha_digitada)) {
        $erro = "Por favor, informe seu e-mail e senha para continuar.";
    } else {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email_digitado);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        if ($usuario_banco = mysqli_fetch_assoc($resultado)) {
            // Atenção: suporte a senha com hash ou texto simples para compatibilidade com contas existentes
            $senhaValida = password_verify($senha_digitada, $usuario_banco['senha']) || $senha_digitada === $usuario_banco['senha'];
            if ($senhaValida) {
                $_SESSION['logado']     = true;
                $_SESSION['usuario_id']  = $usuario_banco['id'];
                $_SESSION['usuario']     = $usuario_banco['nome'];
                $_SESSION['email']       = $usuario_banco['email'];
                $_SESSION['perfil']      = $usuario_banco['perfil'];

                header("Location: home.php");
                exit();
            } else {
                $erro = "Senha incorreta. Tente novamente.";
            }
        } else {
            $erro = "E-mail não encontrado em nosso sistema.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | L-Essense</title>
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

        <div class="max-w-md w-full mx-auto my-auto space-y-8 py-12">
            <div class="space-y-2">
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-600 block">Autenticação</span>
                <h1 class="text-4xl font-bold tracking-tight text-stone-950">Boas-vindas de volta.</h1>
                <p class="text-xs text-stone-400 font-medium leading-relaxed">Insira as suas credenciais para acessar o menu e os seus pedidos.</p>
            </div>

            <?php if (!empty($erro)): ?>
                <div class="animate-shake bg-red-50 border border-red-200 text-red-700 text-xs font-semibold px-4 py-3 rounded-xl flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 shrink-0"></i>
                    <span><?php echo htmlspecialchars($erro); ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-4">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-wider text-stone-400 ml-1 block">E-mail Corporativo / Cliente</label>
                    <div class="relative">
                        <i data-lucide="mail" class="w-4 h-4 text-stone-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                        <input type="email" name="email" required placeholder="seuemail@exemplo.com"
                            class="w-full pl-11 pr-4 py-4 bg-stone-100/50 border border-stone-200/60 focus:border-amber-500 focus:bg-white rounded-2xl transition-all text-sm font-medium outline-none">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-wider text-stone-400 ml-1 block">Senha de Acesso</label>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 text-stone-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                        <input type="password" name="senha" required placeholder="••••••••"
                            class="w-full pl-11 pr-4 py-4 bg-stone-100/50 border border-stone-200/60 focus:border-amber-500 focus:bg-white rounded-2xl transition-all text-sm font-medium outline-none">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-black uppercase tracking-[0.2em] text-[11px] py-5 rounded-2xl hover:scale-[1.01] active:scale-95 transition-all shadow-lg shadow-amber-600/10 flex items-center justify-center gap-2">
                        Entrar no Sistema <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

            <div class="text-center pt-2 text-[11px] text-stone-500 font-medium">
                Não tem conta? <a href="cadastro.php" class="font-black text-amber-600 hover:text-amber-700">Cadastre-se aqui</a>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-[9px] font-black uppercase tracking-[0.2em] text-stone-400">Ambiente Seguro & Monitorado</span>
        </div>

    </main>

    <aside class="hidden md:block w-1/2 bg-stone-950 relative overflow-hidden min-h-screen">
        <img src="https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&q=80&w=1200" 
             alt="Gastronomia Conceito" 
             class="absolute inset-0 w-full h-full object-cover opacity-50 scale-105 transition-transform duration-700 hover:scale-100">
        <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-transparent to-transparent"></div>
        
        <div class="absolute bottom-16 left-16 right-16 space-y-3">
            <p class="font-serif text-3xl italic text-stone-100 max-w-md leading-relaxed">
                "A culinária de excelência não alimenta apenas o corpo, desperta memórias."
            </p>
            <span class="text-[9px] font-black uppercase text-amber-500 tracking-wider block">— Boutique L-Essense</span>
        </div>
    </aside>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>