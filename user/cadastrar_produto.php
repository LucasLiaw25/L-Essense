<?php
// 1. Garante que apenas o Administrador/Gestor possa acessar esta página
require_once __DIR__ . '/../auth/verificarADM.php';
require_once __DIR__ . '/../auth/Conexao.php';

$sucesso = "";
$erro = "";

// 2. Processa o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = (float)($_POST['preco'] ?? 0);
    $estoque = (int)($_POST['estoque'] ?? 0);

    if (empty($nome) || $preco <= 0 || $estoque < 0) {
        $erro = "Por favor, preencha o nome, preço e estoque corretamente!";
    } else {
        // Insere o produto diretamente no banco de dados
        $sql = "INSERT INTO produtos (nome, descricao, preco, estoque) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "ssdi", $nome, $descricao, $preco, $estoque);

        if (mysqli_stmt_execute($stmt)) {
            $sucesso = "Produto '" . htmlspecialchars($nome) . "' cadastrado com sucesso!";
        } else {
            $erro = "Erro ao cadastrar o produto no banco de dados.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L-Essense - Cadastrar Produto</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Instrument Serif', serif; }
    </style>
</head>
<body class="bg-stone-50 text-stone-900 antialiased min-h-screen flex flex-col justify-between">

    <main class="max-w-4xl mx-auto px-4 w-full flex-grow">
        <?php include '../user/menu.php'; ?>

        <div class="bg-white border border-stone-100 rounded-3xl p-8 max-w-xl mx-auto shadow-sm mt-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-stone-100">
                <div class="p-3 bg-stone-950 text-white rounded-2xl">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="font-serif text-3xl text-stone-900">Novo Prato</h1>
                    <span class="text-[9px] font-black uppercase tracking-widest text-stone-400">Adicionar ao Cardápio</span>
                </div>
            </div>

            <?php if (!empty($erro)): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 text-xs font-black uppercase tracking-widest rounded-2xl flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    <?php echo $erro; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($sucesso)): ?>
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-black uppercase tracking-widest rounded-2xl flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <?php echo $sucesso; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-5">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-stone-400 mb-2">Nome do Produto / Prato</label>
                    <input type="text" name="nome" required placeholder="Ex: Risoto de Alho Poró" 
                           class="w-full px-4 py-3 bg-stone-50 border border-stone-100 focus:border-stone-300 focus:bg-white rounded-xl transition-all text-sm font-medium outline-none">
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-stone-400 mb-2">Descrição / Ingredientes</label>
                    <textarea name="descricao" rows="3" placeholder="Ex: Arroz arbóreo, alho poró fresco, vinho branco e parmesão." 
                              class="w-full px-4 py-3 bg-stone-50 border border-stone-100 focus:border-stone-300 focus:bg-white rounded-xl transition-all text-sm font-medium outline-none resize-none"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-stone-400 mb-2">Preço (R$)</label>
                        <input type="number" name="preco" step="0.01" min="0.01" required placeholder="0.00" 
                               class="w-full px-4 py-3 bg-stone-50 border border-stone-100 focus:border-stone-300 focus:bg-white rounded-xl transition-all text-sm font-medium outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-stone-400 mb-2">Estoque Inicial</label>
                        <input type="number" name="estoque" min="0" required placeholder="10" 
                               class="w-full px-4 py-3 bg-stone-50 border border-stone-100 focus:border-stone-300 focus:bg-white rounded-xl transition-all text-sm font-medium outline-none">
                    </div>
                </div>

                <button type="submit" 
                        class="w-full py-4 bg-stone-950 text-white font-black uppercase tracking-widest text-[10px] rounded-xl hover:bg-amber-700 hover:scale-[1.01] active:scale-95 transition-all mt-4">
                    Cadastrar no Cardápio
                </button>
            </form>
        </div>
    </main>

    <?php include '../user/rodape.php'; ?>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>