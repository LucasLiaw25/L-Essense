<?php
// dashboard/status.php
require_once __DIR__ . '/../auth/VerificarLogin.php';
require_once __DIR__ . '/../auth/Conexao.php';

global $conexao;

$mensagem = "";

// Lógica para alterar o status (Apenas Administrador)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_status'])) {
    if (isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'admin') {
        $pedido_id = (int)($_POST['pedido_id'] ?? 0);
        $novo_status = trim($_POST['novo_status'] ?? '');
        $status_validos = ['Pendente', 'Concluído', 'Cancelado'];

        if ($pedido_id > 0 && in_array($novo_status, $status_validos, true)) {
            $sql_update = "UPDATE pedidos SET status = ? WHERE id = ?";
            $stmt = mysqli_prepare($conexao, $sql_update);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "si", $novo_status, $pedido_id);
                if (mysqli_stmt_execute($stmt)) {
                    $mensagem = "Status do pedido #$pedido_id alterado para $novo_status!";
                } else {
                    $mensagem = "Falha ao atualizar o status do pedido. Tente novamente.";
                }
                mysqli_stmt_close($stmt);
            } else {
                $mensagem = "Erro interno ao preparar a atualização do pedido.";
            }
        } else {
            $mensagem = "Dados inválidos para alteração de status.";
        }
    } else {
        $mensagem = "Operação não autorizada.";
    }
}

// Busca os pedidos baseado no nível de acesso
if ($_SESSION['perfil'] === 'admin') {
    $sql_pedidos = "SELECT * FROM pedidos ORDER BY criado_em DESC";
    $stmt_p = mysqli_prepare($conexao, $sql_pedidos);
} else {
    $sql_pedidos = "SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY criado_em DESC";
    $stmt_p = mysqli_prepare($conexao, $sql_pedidos);
    mysqli_stmt_bind_param($stmt_p, "i", $_SESSION['usuario_id']);
}

mysqli_stmt_execute($stmt_p);
$resultado_p = mysqli_stmt_get_result($stmt_p);
$pedidos = mysqli_fetch_all($resultado_p, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_p);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status dos Pedidos | L-Essense</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Instrument Serif', serif; }
    </style>
</head>
<body class="bg-stone-50 text-stone-900 antialiased min-h-screen p-4 md:p-8">

    <div class="max-w-5xl mx-auto space-y-8">
        <?php include __DIR__ . '/../user/menu.php'; ?>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-stone-200 pb-6">
            <div>
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-600 block mb-1">Acompanhamento</span>
                <h1 class="text-3xl font-bold tracking-tight text-stone-950">Histórico de Pedidos</h1>
            </div>
            
            <?php if (!empty($mensagem)): ?>
                <div class="px-4 py-3 rounded-xl text-xs font-semibold bg-stone-900 text-white flex items-center gap-2 shadow-sm border border-stone-800 transition-all">
                    <i data-lucide="info" class="w-4 h-4 text-amber-400"></i>
                    <span><?php echo htmlspecialchars($mensagem); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($pedidos)): ?>
            <div class="bg-white border border-stone-200/60 rounded-2xl p-12 text-center max-w-xl mx-auto shadow-sm">
                <div class="w-12 h-12 bg-stone-50 rounded-xl flex items-center justify-center mx-auto mb-4 border border-stone-100">
                    <i data-lucide="shopping-bag" class="w-5 h-5 text-stone-400"></i>
                </div>
                <h3 class="text-sm font-bold text-stone-950 mb-1">Nenhum pedido localizado</h3>
                <p class="text-xs text-stone-400 leading-relaxed">Você ainda não realizou solicitações ou não há registros no sistema.</p>
                <a href="../user/home.php" class="inline-flex mt-5 bg-stone-900 text-white text-[10px] font-black uppercase tracking-widest px-6 py-3 rounded-xl hover:bg-black transition-all">
                    Ir para o Menu
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($pedidos as $p): ?>
                    <div class="bg-white border border-stone-200/80 rounded-2xl shadow-sm overflow-hidden flex flex-col md:flex-row justify-between items-stretch">
                        
                        <div class="p-6 flex-1 space-y-4">
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 border-b border-stone-100 pb-3">
                                <span class="font-mono text-xs text-stone-400 font-bold">#<?php echo $p['id']; ?></span>
                                <span class="text-xs text-stone-400 font-medium">
                                    <?php echo date('d/m/Y H:i', strtotime($p['criado_em'])); ?>
                                </span>
                                <span class="text-xs text-stone-400 font-bold">| Cliente: <?php echo htmlspecialchars($p['usuario_nome']); ?></span>
                            </div>

                            <div class="space-y-3">
                                <span class="text-[9px] font-black uppercase tracking-wider text-stone-400 block">Itens Solicitados</span>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <?php
                                    // SELECT com JOIN para capturar a coluna da imagem original da tabela de produtos
                                    $sql_itens = "SELECT pi.nome, pi.quantidade, pi.preco, pr.imagem 
                                                  FROM pedido_itens pi
                                                  LEFT JOIN produtos pr ON pi.produto_id = pr.id
                                                  WHERE pi.pedido_id = ?";
                                    $stmt_i = mysqli_prepare($conexao, $sql_itens);
                                    mysqli_stmt_bind_param($stmt_i, "i", $p['id']);
                                    mysqli_stmt_execute($stmt_i);
                                    $res_itens = mysqli_stmt_get_result($stmt_i);
                                    
                                    while ($item = mysqli_fetch_assoc($res_itens)):
                                        // Configura caminho da foto fallback caso o produto tenha sido deletado ou esteja sem imagem
                                        $foto = (!empty($item['imagem']) && file_exists(__DIR__ . '/../uploads/' . $item['imagem'])) 
                                                ? '../uploads/' . $item['imagem'] 
                                                : '../img/default-product.jpg'; // Substitua pelo seu caminho padrão se tiver
                                    ?>
                                        <div class="flex items-center gap-3 p-2 bg-stone-50 border border-stone-100 rounded-xl">
                                            <img src="<?php echo $foto; ?>" alt="<?php echo htmlspecialchars($item['nome']); ?>" 
                                                 class="w-12 h-12 rounded-lg object-cover bg-stone-200 border border-stone-200/40 shrink-0">
                                            
                                            <div class="min-w-0 flex-1">
                                                <h4 class="text-xs font-bold text-stone-950 truncate"><?php echo htmlspecialchars($item['nome']); ?></h4>
                                                <p class="text-[10px] text-stone-400 font-medium mt-0.5">
                                                    Qtd: <span class="text-stone-900 font-bold"><?php echo $item['quantidade']; ?></span> 
                                                    · R$ <?php echo number_format((float)$item['preco'], 2, ',', '.'); ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php 
                                    endwhile; 
                                    mysqli_stmt_close($stmt_i);
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-stone-50/50 border-t md:border-t-0 md:border-l border-stone-100 flex flex-col justify-between items-start md:items-end w-full md:w-64 gap-6 shrink-0">
                            
                            <div class="md:text-right">
                                <span class="text-[10px] font-black uppercase text-stone-400 tracking-widest block mb-0.5">Valor Total</span>
                                <span class="text-2xl font-serif text-stone-950 font-bold">
                                    R$ <?php echo number_format((float)($p['total'] ?? 0), 2, ',', '.'); ?>
                                </span>
                            </div>

                            <div class="flex flex-col items-start md:items-end gap-2 w-full">
                                <span class="text-[9px] font-black uppercase text-stone-400 tracking-wider">Situação</span>
                                <?php 
                                $status = $p['status'] ?? 'Pendente';
                                if ($status === 'Concluído') {
                                    echo '<span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-[10px] font-black uppercase tracking-wider">Concluído</span>';
                                } elseif ($status === 'Cancelado') {
                                    echo '<span class="px-3 py-1 bg-red-50 text-red-700 border border-red-200 rounded-full text-[10px] font-black uppercase tracking-wider">Cancelado</span>';
                                } else {
                                    echo '<span class="px-3 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-[10px] font-black uppercase tracking-wider animate-pulse">Pendente</span>';
                                }
                                ?>
                            </div>

                            <?php if ($_SESSION['perfil'] === 'admin'): ?>
                                <form action="status.php" method="POST" class="w-full flex gap-2">
                                    <input type="hidden" name="pedido_id" value="<?php echo $p['id']; ?>">
                                    
                                    <select name="novo_status" class="flex-1 bg-white text-stone-700 rounded-xl px-2.5 h-10 text-[10px] font-black uppercase tracking-widest outline-none border border-stone-200 focus:border-stone-400 transition-all cursor-pointer">
                                        <option value="Pendente" <?php echo $p['status'] == 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
                                        <option value="Concluído" <?php echo $p['status'] == 'Concluído' ? 'selected' : ''; ?>>Concluído</option>
                                        <option value="Cancelado" <?php echo $p['status'] == 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                    
                                    <button type="submit" name="alterar_status" class="h-10 bg-stone-900 text-white px-3.5 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all active:scale-95 shadow-sm">
                                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../user/rodape.php'; ?>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>