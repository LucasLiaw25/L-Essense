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
    $email_digitado = trim($_POST['Email'] ?? '');
    $senha_digitada = $_POST['senha'] ?? '';

    if (empty($nome_digitado) || empty($email_digitado) || empty($senha_digitada)) {
        $erro = "Por favor, preencha todos os campos para continuar!";
    } else {
        
        // 2. Busca se o e-mail digitado já existe no banco de dados
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email_digitado);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        if ($usuario_banco = mysqli_fetch_assoc($resultado)) {
            // O usuário existe! Comparamos o nome de forma case-insensitive (strcasecmp) e validamos a senha
            if (strcasecmp($nome_digitado, $usuario_banco['nome']) === 0 && (password_verify($senha_digitada, $usuario_banco['senha']) || $senha_digitada === $usuario_banco['senha'])) {
                $_SESSION['logado'] = true;
                $_SESSION['usuario_id'] = (int)$usuario_banco['id']; // CRÍTICO: Guardando o ID correto
                $_SESSION['usuario'] = $usuario_banco['nome'];
                $_SESSION['perfil'] = $usuario_banco['perfil']; // Aqui ele vai pegar 'admin' corretamente

                header("Location: home.php");
                exit();
            } else {
                $erro = "Nome ou Senha incorretos para o e-mail informado!";
            }
        } else {
            // Usuário NÃO existe: Cria o cadastro automático (Login Livre)
            $senha_criptografada = password_hash($senha_digitada, PASSWORD_DEFAULT);
            $perfil_padrao = 'cliente';

            $sql_cadastro = "INSERT INTO usuarios (nome, email, senha, perfil) VALUES (?, ?, ?, ?)";
            $stmt_cad = mysqli_prepare($conexao, $sql_cadastro);
            mysqli_stmt_bind_param($stmt_cad, "ssss", $nome_digitado, $email_digitado, $senha_criptografada, $perfil_padrao);
            
            if (mysqli_stmt_execute($stmt_cad)) {
                $novo_id = mysqli_insert_id($conexao);

                $_SESSION['logado'] = true;
                $_SESSION['usuario_id'] = (int)$novo_id; 
                $_SESSION['usuario'] = $nome_digitado;
                $_SESSION['perfil'] = $perfil_padrao;

                header("Location: home.php");
                exit();
            } else {
                $erro = "Erro ao criar sua conta automaticamente. Tente novamente.";
            }
            mysqli_stmt_close($stmt_cad);
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
    <title>L-Essense - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Instrument Serif', serif; }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-4px); }
            40%, 80% { transform: translateX(4px); }
        }
        .animate-shake { animation: shake 0.4s ease-in-out; }
    </style>
</head>

<body class="bg-stone-50 text-stone-900 h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md">
        <div class="text-center mb-10">
            <h1 class="font-serif text-5xl italic text-stone-850 mb-2">L-Essense</h1>
            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-stone-400">Restaurante & Gestão</p>
        </div>

        <div class="bg-white p-10 rounded-[2.5rem] shadow-xl shadow-stone-200/50 border border-stone-100">
            <h2 class="text-xs font-black uppercase tracking-widest text-stone-400 mb-8 text-center">Identificação</h2>

            <?php if (!empty($erro)): ?>
                <div class="mb-6 flex items-center gap-2 p-4 bg-red-50 border border-red-100 text-red-700 rounded-2xl text-xs font-bold animate-shake">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span><?php echo htmlspecialchars($erro); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="text-[10px] font-black uppercase tracking-wider text-stone-400 ml-2 mb-1 block">Nome de Usuário</label>
                    <input type="text" name="nome" placeholder="Como deseja ser chamado?" required
                        class="w-full px-6 py-4 bg-stone-50 border border-stone-200/40 focus:border-stone-300 focus:bg-white rounded-2xl transition-all text-sm font-medium outline-none">
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase tracking-wider text-stone-400 ml-2 mb-1 block">E-mail</label>
                    <input type="email" name="Email" placeholder="seu@email.com" required
                        class="w-full px-6 py-4 bg-stone-50 border border-stone-200/40 focus:border-stone-300 focus:bg-white rounded-2xl transition-all text-sm font-medium outline-none">
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase tracking-wider text-stone-400 ml-2 mb-1 block">Senha</label>
                    <input type="password" name="senha" placeholder="••••••••" required
                        class="w-full px-6 py-4 bg-stone-50 border border-stone-200/40 focus:border-stone-300 focus:bg-white rounded-2xl transition-all text-sm font-medium outline-none">
                </div>

                <button type="submit"
                    class="w-full bg-stone-900 text-white font-black uppercase tracking-[0.2em] text-[11px] py-5 rounded-2xl hover:bg-black hover:scale-[1.01] active:scale-95 transition-all shadow-lg shadow-stone-200 mt-4 flex items-center justify-center gap-2">
                    Entrar
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>
        </div>

        <p class="text-center mt-10 text-[10px] font-bold text-stone-300 uppercase tracking-widest">
            &copy; <?php echo date('Y'); ?> Boutique Experience
        </p>
    </div>

    <script>
        lucide.createIcons();

        const erroBox = document.querySelector('.animate-shake');
        if (erroBox) {
            setTimeout(() => {
                erroBox.style.transition = "opacity 0.6s ease, transform 0.6s ease";
                erroBox.style.opacity = "0";
                erroBox.style.transform = "translateY(-8px)";
                setTimeout(() => erroBox.remove(), 600);
            }, 5000);
        }
    </script>
</body>
</html>