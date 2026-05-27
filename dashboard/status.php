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
$res_pedidos = mysqli_stmt_get_result($stmt_p);
$pedidos = mysqli_fetch_all($res_pedidos, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_p);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Pedidos - L-Essense</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>body { font-family: 'Inter', sans-serif; } .font-serif { font-family: 'Instrument Serif', serif; }</style>
</head>
<body class="bg-stone-50 text-stone-900 min-h-screen p-4 md:p-8">
    <div class="max-w-4xl mx-auto">
        <?php include '../user/menu.php'; ?>

        <div class="mb-8 border-b border-stone-200 pb-3">
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400 block mb-1">Módulo de Rastreamento</span>
            <h1 class="font-serif text-4xl italic text-stone-950">Histórico de Pedidos</h1>
        </div>

        <?php if (!empty($mensagem)): ?>
            <div class="p-4 bg-stone-900 text-white text-xs font-bold rounded-2xl mb-6 flex items-center gap-2 shadow-md">
                <i data-lucide="check" class="text-emerald-400 w-4 h-4"></i> <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($pedidos)): ?>
            <div class="bg-white rounded-2xl p-12 text-center border border-stone-200/60 italic text-stone-400 text-sm shadow-sm">
                Nenhum registro de pedido localizado.
            </div>
        <?php else: ?>
            <div class="space-y-5">
                <?php foreach ($pedidos as $p): 
                    $sql_items = "SELECT * FROM pedido_itens WHERE pedido_id = ?";
                    $stmt_i = mysqli_prepare($conexao, $sql_items);
                    mysqli_stmt_bind_param($stmt_i, "i", $p['id']);
                    mysqli_stmt_execute($stmt_i);
                    $res_items = mysqli_stmt_get_result($stmt_i);
                    $itens = mysqli_fetch_all($res_items, MYSQLI_ASSOC);
                    mysqli_stmt_close($stmt_i);

                    // Cor do badge de status
                    $badgeColor = "bg-stone-100 text-stone-600";
                    if($p['status'] == 'Concluído') $badgeColor = "bg-emerald-50 text-emerald-700 border border-emerald-100";
                    if($p['status'] == 'Cancelado') $badgeColor = "bg-red-50 text-red-700 border border-red-100";
                ?>
                    <div class="bg-white border border-stone-200/60 p-6 rounded-2xl shadow-sm">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-stone-100 pb-4 mb-4">
                            <div>
                                <span class="text-xs font-mono font-bold text-stone-900 block">PEDIDO #<?php echo $p['id']; ?></span>
                                <span class="text-[11px] text-stone-400 font-medium">Cliente: <?php echo htmlspecialchars($p['usuario_nome']); ?> | <?php echo date('d/m/Y H:i', strtotime($p['criado_em'])); ?></span>
                            </div>
                            <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
                                <span class="text-sm font-mono font-bold text-stone-900">R$ <?php echo number_format((float)$p['total'], 2, ',', '.'); ?></span>
                                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest <?php echo $badgeColor; ?>">
                                    <?php echo htmlspecialchars($p['status']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="text-xs space-y-2 mb-4 text-stone-700 font-medium">
                            <?php foreach ($itens as $i): ?>
                                <div class="flex justify-between">
                                    <span><?php echo $i['quantidade']; ?>x <span class="text-stone-900 font-semibold"><?php echo htmlspecialchars($i['nome']); ?></span></span>
                                    <span class="text-stone-500 font-mono">R$ <?php echo number_format((float)$i['preco'], 2, ',', '.'); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($_SESSION['perfil'] === 'admin'): ?>
                            <form action="" method="POST" class="flex gap-2 pt-3 border-t border-stone-100">
                                <input type="hidden" name="pedido_id" value="<?php echo $p['id']; ?>">
                                <select name="novo_status" class="bg-stone-50 text-stone-700 rounded-xl px-3 py-2 text-[10px] font-black uppercase tracking-widest outline-none border border-stone-200 focus:border-stone-400 transition-all">
                                    <option value="Pendente" <?php echo $p['status'] == 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
                                    <option value="Concluído" <?php echo $p['status'] == 'Concluído' ? 'selected' : ''; ?>>Concluído</option>
                                    <option value="Cancelado" <?php echo $p['status'] == 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                                <button type="submit" name="alterar_status" class="bg-stone-900 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all">
                                    Atualizar Status
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php include '../user/rodape.php'; ?>
    <script>lucide.createIcons();</script>
</body>
</html>