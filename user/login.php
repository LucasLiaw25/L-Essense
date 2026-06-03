<?php
ob_start();
session_start();

// 1. Importa a conexão com o banco de dados
require_once __DIR__ . '/../auth/Conexao.php'; 

// Se já estiver logado, vai direto para a home
if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header("Location: home.php");
    exit();
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_digitado  = trim($_POST['nome'] ?? '');
    $email_digitado = trim($_POST['email'] ?? '');
    $senha_digitada = $_POST['senha'] ?? '';

    if (empty($email_digitado) || empty($senha_digitada)) {
        $erro = "Por favor, informe seu e-mail e senha para continuar.";
    } else {
        
        // 2. Busca se o e-mail digitado já existe no banco de dados
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email_digitado);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        if ($usuario_banco = mysqli_fetch_assoc($resultado)) {
            // O usuário existe! Comparamos a senha armazenada (hash ou texto simples)
            $senhaValida = password_verify($senha_digitada, $usuario_banco['senha']) || $senha_digitada === $usuario_banco['senha'];
            if ($senhaValida) {
                
                // Login com sucesso! Preenche as variáveis de sessão
                $_SESSION['logado']     = true;
                $_SESSION['usuario_id'] = $usuario_banco['id'];
                $_SESSION['usuario']    = $usuario_banco['nome'];
                $_SESSION['email']      = $usuario_banco['email'];
                $_SESSION['perfil']     = $usuario_banco['perfil']; // 'admin' ou 'cliente'

                header("Location: home.php");
                exit();
            } else {
                $erro = "E-mail ou senha incorretos. Verifique seus dados.";
            }
        } else {
            $erro = "E-mail não encontrado em nossa base de dados.";
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
    <title>Entrar | L-Essense Boutique</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Instrument Serif', serif; }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }
        .animate-shake { animation: shake 0.4s ease-in-out; }
    </style>
</head>
<body class="bg-stone-50 text-stone-900 antialiased min-h-screen flex">

    <div class="hidden lg:flex lg:w-1/2 relative bg-stone-950 items-center justify-center p-12 overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center opacity-40 transform scale-105" style="background-image: url('https://images.unsplash.com/photo-1544025162-d76694265947?q=80&w=1200');"></div>
        
        <div class="absolute inset-0 bg-gradient-to-tr from-stone-950 via-stone-950/70 to-transparent"></div>
        
        <div class="relative max-w-md z-10 space-y-6">
            <div class="h-12 w-12 bg-amber-500 rounded-2xl flex items-center justify-center text-stone-950 font-serif italic font-bold text-2xl shadow-lg shadow-amber-500/20">L</div>
            
            <div class="space-y-3">
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-amber-400 block">Boutique Experience</span>
                <h1 class="font-serif text-5xl text-stone-100 leading-tight">
                    Onde cada detalhe <br><span class="italic text-amber-400">se torna arte.</span>
                </h1>
            </div>
            
            <p class="text-stone-400 text-sm font-medium leading-relaxed">
                Entre com sua conta para acessar nosso cardápio exclusivo, acompanhar seus pedidos em tempo real e desfrutar da mais pura alta gastronomia.
            </p>
            
            <div class="bg-white/5 border border-white/10 backdrop-blur-md p-5 rounded-2xl max-w-sm shadow-xl">
                <p class="text-xs font-medium text-stone-200 leading-relaxed italic">"Uma harmonia impecável entre frescor regional e técnica culinária internacional."</p>
                <div class="mt-3 flex items-center gap-2">
                    <div class="w-1.5 h-1.5 rounded-full bg-amber-500"></div>
                    <span class="text-[9px] font-black uppercase text-amber-400 tracking-wider">Guia Gastronômico 2026</span>
                </div>
            </div>
        </div>
        
        <div class="absolute bottom-6 left-12 text-[10px] font-bold text-stone-500 uppercase tracking-widest">
            &copy; <?php echo date('Y'); ?> L-Essense Restaurante
        </div>
    </div>

    <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-6 sm:p-12 md:p-20 relative">
        
        <a href="../index.php" class="absolute top-6 right-6 px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-600 hover:text-stone-900 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Voltar ao início
        </a>

        <div class="w-full max-w-md space-y-8">
            <div class="text-center lg:text-left space-y-2">
                <div class="h-10 w-10 bg-amber-600 rounded-xl flex items-center justify-center text-white font-serif italic font-bold text-xl mx-auto lg:mx-0 lg:hidden shadow-md shadow-amber-600/10 mb-4">L</div>
                
                <h2 class="text-3xl font-bold tracking-tight text-stone-950">Seja bem-vindo</h2>
                <p class="text-stone-400 text-xs font-semibold uppercase tracking-wider">Insira suas credenciais de acesso</p>
            </div>

            <?php if (!empty($erro)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-2xl text-xs font-semibold flex items-center gap-3 animate-shake">
                    <div class="h-6 w-6 bg-red-100 rounded-lg flex items-center justify-center text-red-600 shrink-0">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    </div>
                    <span><?php echo $erro; ?></span>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-5">
                
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 block">Nome do Usuário <span class="text-[9px] text-stone-400">(opcional)</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-stone-400">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="nome" placeholder="Seu nome cadastrado"
                            value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>"
                            class="w-full pl-11 pr-4 py-4 bg-stone-100/50 border border-stone-200/60 focus:border-amber-500 focus:bg-white rounded-2xl transition-all text-sm font-medium outline-none">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 block">E-mail Corporativo / Cliente</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-stone-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </span>
                        <input type="email" name="email" placeholder="exemplo@lessence.com" required
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                            class="w-full pl-11 pr-4 py-4 bg-stone-100/50 border border-stone-200/60 focus:border-amber-500 focus:bg-white rounded-2xl transition-all text-sm font-medium outline-none">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-stone-400 ml-1 block">Senha de Acesso</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-stone-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </span>
                        <input type="password" name="senha" placeholder="••••••••" required
                            class="w-full pl-11 pr-4 py-4 bg-stone-100/50 border border-stone-200/60 focus:border-amber-500 focus:bg-white rounded-2xl transition-all text-sm font-medium outline-none">
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-amber-600 hover:bg-amber-700 text-white font-black uppercase tracking-[0.2em] text-[11px] py-5 rounded-2xl hover:scale-[1.01] active:scale-95 transition-all shadow-lg shadow-amber-600/10 mt-6 flex items-center justify-center gap-2">
                    Entrar no Sistema
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>
        </div>

        <p class="text-center mt-12 text-[10px] font-bold text-stone-300 uppercase tracking-widest lg:hidden">
            &copy; <?php echo date('Y'); ?> Boutique Experience
        </p>
    </div>

    <script>
        lucide.createIcons();

        // Esconde suavemente a caixa de erro após 5 segundos
        const erroBox = document.querySelector('.animate-shake');
        if (erroBox) {
            setTimeout(() => {
                erroBox.style.transition = "opacity 0.6s ease, transform 0.6s ease";
                erroBox.style.opacity = "0";
                erroBox.style.transform = "translateY(-10px)";
                setTimeout(() => erroBox.remove(), 600);
            }, 5000);
        }
    </script>
</body>
</html>