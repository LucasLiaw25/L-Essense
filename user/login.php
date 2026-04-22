<?php
ob_start();
session_start();
include __DIR__ . '/../auth/usuario_permitidos.php';
// Se já estiver logado, vai direto para a home
if(isset($_SESSION['logado']) && $_SESSION['logado'] === true){
    header("Location: home.php");
    exit();
}

// Caminho atualizado para encontrar o arquivo de usuários na pasta auth

$erro = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $nome_digitado  = trim($_POST['nome'] ?? '');
    $email_digitado = trim($_POST['Email'] ?? '');
    $senha_digitada = $_POST['senha'] ?? '';

    if(empty($nome_digitado) || empty($email_digitado) || empty($senha_digitada)){
        $erro = "Por favor, preencha todos os campos para continuar!";
    }
    else {
        if(isset($usuario_permitidos[$nome_digitado])) {
            $dados_usuario = $usuario_permitidos[$nome_digitado];
            
            if($email_digitado === $dados_usuario['email'] && $senha_digitada === $dados_usuario['senha']) {
                $_SESSION['logado'] = true;
                $_SESSION['usuario'] = $nome_digitado;
                $_SESSION['perfil'] = 'admin'; 
                $newClient = new Client($nome_digitado, $email_digitado, $senha_digitada);
                addClient($newClient);
                header("Location: home.php");
                exit();
            } else {
                $erro = "Credenciais incorretas para administrador.";
            }
        }
        else {
            // LOGIN LIVRE: Qualquer outro usuário entra como 'cliente'
            $_SESSION['logado'] = true;
            $_SESSION['usuario'] = $nome_digitado;
            $_SESSION['perfil'] = 'cliente';
            header("Location: home.php");
            exit();
        }
    }
}
ob_end_flush();
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
    </style>
</head>
<body class="bg-stone-50 text-stone-900 h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md">
        <div class="text-center mb-10">
            <h1 class="font-serif text-5xl italic text-stone-800 mb-2">L-Essense</h1>
            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-stone-400">Restaurante & Gestão</p>
        </div>

        <div class="bg-white p-10 rounded-[2.5rem] shadow-xl shadow-stone-200/50 border border-stone-100">
            <h2 class="text-xs font-black uppercase tracking-widest text-stone-400 mb-8 text-center">Identificação</h2>

            <?php if(!empty($erro)): ?>
                <div class="mb-6 flex items-center gap-2 p-4 bg-red-50 text-red-500 rounded-2xl text-xs font-bold border border-red-100 animate-shake">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    <?php echo $erro; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="text-[10px] font-black uppercase tracking-wider text-stone-400 ml-2 mb-1 block">Nome de Usuário</label>
                    <input type="text" name="nome" placeholder="Como deseja ser chamado?" 
                        class="w-full px-6 py-4 bg-stone-50 border-transparent focus:border-stone-200 focus:bg-white focus:ring-0 rounded-2xl transition-all text-sm font-medium outline-none">
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase tracking-wider text-stone-400 ml-2 mb-1 block">E-mail</label>
                    <input type="email" name="Email" placeholder="seu@email.com" 
                        class="w-full px-6 py-4 bg-stone-50 border-transparent focus:border-stone-200 focus:bg-white focus:ring-0 rounded-2xl transition-all text-sm font-medium outline-none">
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase tracking-wider text-stone-400 ml-2 mb-1 block">Senha</label>
                    <input type="password" name="senha" placeholder="••••••••" 
                        class="w-full px-6 py-4 bg-stone-50 border-transparent focus:border-stone-200 focus:bg-white focus:ring-0 rounded-2xl transition-all text-sm font-medium outline-none">
                </div>

                <button type="submit" 
                    class="w-full bg-stone-900 text-white font-black uppercase tracking-[0.2em] text-[11px] py-5 rounded-2xl hover:bg-black hover:scale-[0.02] active:scale-95 transition-all shadow-lg shadow-stone-200 mt-4 flex items-center justify-center gap-2">
                    Entrar no Sistema
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>
        </div>

        <p class="text-center mt-10 text-[10px] font-bold text-stone-300 uppercase tracking-widest">
            &copy; <?php echo date('Y'); ?> Boutique Experience
        </p>
    </div>

    <script>
        // Inicializa os ícones
        lucide.createIcons();

        // Faz o erro sumir após 5 segundos
        const erroBox = document.querySelector('.animate-shake');
        if (erroBox) {
            setTimeout(() => {
                erroBox.style.transition = "opacity 0.8s ease, transform 0.8s ease";
                erroBox.style.opacity = "0";
                erroBox.style.transform = "translateY(-10px)";
                setTimeout(() => erroBox.remove(), 800);
            }, 5000);
        }
    </script>
</body>
</html>