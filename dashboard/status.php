<?php
// dashboard/status.php
require_once __DIR__ . '/../auth/VerificarLogin.php';
require_once __DIR__ . '/../auth/Conexao.php';

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_status'])) {
    $pedido_id = (int)$_POST['pedido_id'];
    $novo_status = $_POST['novo_status'];

    $sql_update = "UPDATE pedidos SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql_update);
    mysqli_stmt_bind_param($stmt, "si", $novo_status, $pedido_id);
    if (mysqli_stmt_execute($stmt)) {
        $mensagem = "Status do pedido #$pedido_id alterado para $novo_status!";
    }
    mysqli_stmt_close($stmt);
}

if ($_SESSION['perfil'] === 'admin') {
    $sql_pedidos = "SELECT * FROM pedidos ORDER BY criado_em DESC";
    $stmt_p = mysqli_prepare($conexao, $sql_pedidos);
} else {
    $sql_pedidos = "SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY criado_em DESC";
    $stmt_p = mysqli_prepare($conexao, $sql_pedidos);
    mysqli_stmt_bind_param($stmt_p, "i", $_SESSION['usuario_id']);
}

mysqli_stmt_execute($stmt_p);
$resultado = mysqli_stmt_get_result($stmt_p);
$pedidos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_p);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status - L-Essense</title>
    
    <link rel="stylesheet" href="../style.css">
    
    <script src="https://cdn.tailwindcss.com\"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/lucide@latest\"></script>

    <style>
        body { 
    font-family: 'Inter', sans-serif; 
}
.font-serif { 
    font-family: 'Instrument Serif', serif; 
}

    </style>
</head>
<body class="bg-stone-50 text-stone-900 antialiased font-sans">
</head>
<body class="bg-stone-50 text-stone-800 p-4 md:p-8 min-h-screen">
    <div class="max-w-4xl mx-auto">
        <?php include '../user/menu.php'; ?>

        <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="font-serif text-4xl text-stone-950">Acompanhamento</h1>
                <p class="text-stone-400 text-xs font-bold uppercase tracking-widest mt-1">Histórico e estados dos pedidos</p>
            </div>
            <span class="text-[10px] font-black bg-stone-200 text-stone-700 px-3 py-1.5 rounded-full uppercase tracking-wider">
                <?php echo count($pedidos); ?> Pedidos Encontrados
            </span>
        </div>

        <?php if (!empty($mensagem)): ?>
            <div class="mb-6 p-4 bg-stone-900 text-stone-100 rounded-2xl text-xs font-bold uppercase tracking-widest flex items-center gap-2 shadow-md">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i> <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($pedidos)): ?>
            <div class="bg-white border border-stone-200/80 rounded-3xl p-12 text-center shadow-sm">
                <i data-lucide="package-open" class="w-12 h-12 text-stone-300 mx-auto mb-4"></i>
                <h3 class="font-serif text-xl text-stone-900 mb-1">Nenhum pedido efetuado</h3>
                <p class="text-stone-400 text-sm max-w-sm mx-auto">Assim que um pedido for submetido na plataforma, ele aparecerá listado nesta secção.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 gap-4">
                <?php foreach ($pedidos as $p): 
                    $corStatus = 'bg-stone-100 text-stone-700 border-stone-200';
                    if ($p['status'] == 'Concluído') $corStatus = 'bg-emerald-50 text-emerald-700 border-emerald-200/60';
                    if ($p['status'] == 'Cancelado') $corStatus = 'bg-red-50 text-red-700 border-red-200/60';
                ?>
                    <div class="bg-white border border-stone-200/80 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4 transition-all hover:border-stone-300">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2.5">
                                <span class="font-mono text-xs font-bold text-stone-400">#<?php echo $p['id']; ?></span>
                                <span class="text-sm font-bold text-stone-900"><?php echo htmlspecialchars($p['usuario_nome']); ?></span>
                                <span class="text-[10px] font-black uppercase tracking-widest border px-2 py-0.5 rounded-md <?php echo $corStatus; ?>">
                                    <?php echo $p['status']; ?>
                                </span>
                            </div>
                            <p class="text-stone-400 text-[11px] font-medium">Realizado em: <?php echo date('d/m/Y H:i', strtotime($p['criado_em'])); ?></p>
                            <div class="text-lg font-serif text-stone-950 font-bold pt-1">
                                R$ <?php echo number_format((float)$p['total'], 2, ',', '.'); ?>
                            </div>
                        </div>

                        <?php if ($_SESSION['perfil'] === 'admin'): ?>
                            <form action="" method="POST" class="flex items-center gap-2 w-full md:w-auto pt-3 md:pt-0 border-t md:border-t-0 border-stone-100">
                                <input type="hidden" name="pedido_id" value="<?php echo $p['id']; ?>">
                                
                                <select name="novo_status" class="bg-stone-50 text-stone-700 rounded-xl px-3 h-10 text-[10px] font-black uppercase tracking-widest outline-none border border-stone-200 focus:border-stone-400 transition-all cursor-pointer">
                                    <option value="Pendente" <?php echo $p['status'] == 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
                                    <option value="Concluído" <?php echo $p['status'] == 'Concluído' ? 'selected' : ''; ?>>Concluído</option>
                                    <option value="Cancelado" <?php echo $p['status'] == 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                                
                                <button type="submit" name="alterar_status" class="h-10 bg-stone-900 text-white px-5 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all active:scale-95 shadow-sm whitespace-nowrap">
                                    Atualizar Estado
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../user/rodape.php'; ?>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>