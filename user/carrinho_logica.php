<?php
// Use VerificarLogin para que CLIENTES também possam comprar
require_once __DIR__ . '/../auth/VerificarLogin.php';
// Importa a conexão com o banco para verificar estoque e dados do produto
require_once __DIR__ . '/../auth/Conexao.php';

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// ==========================================
// LÓGICA PARA ADICIONAR PRODUTO AO CARRINHO
// ==========================================
if (isset($_GET['adicionar'])) {
    $id = (int)$_GET['adicionar'];
    
    // Busca o produto específico no banco de dados pelo ID
    $sql = "SELECT * FROM produtos WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if ($produto = mysqli_fetch_assoc($resultado)) {
        
        // 1. Pega a quantidade que já está no carrinho (se não tiver, é 0)
        $quantidadeNoCarrinho = isset($_SESSION['carrinho'][$id]) ? $_SESSION['carrinho'][$id]['quantidade'] : 0;

        // 2. Verifica se (o que já tem no carrinho + 1) ultrapassa o estoque do banco
        if (($quantidadeNoCarrinho + 1) > $produto['estoque']) {
            // Se ultrapassar, volta para a home com um aviso de erro
            header("Location: ../user/home.php?erro=sem_estoque");
            exit();
        }

        // 3. Se passou na verificação, adiciona ou incrementa usando o formato de array associativo
        if (isset($_SESSION['carrinho'][$id])) {
            $_SESSION['carrinho'][$id]['quantidade']++;
        } else {
            $_SESSION['carrinho'][$id] = [
                'nome' => $produto['nome'],
                'preco' => (float)$produto['preco'],
                'quantidade' => 1
            ];
        }
    }
    
    // Redireciona de volta informando o sucesso
    header("Location: ../user/home.php?sucesso=adicionado");
    exit();
}

// ==========================================
// LÓGICA PARA FINALIZAR O PEDIDO (LANÇAR)
// ==========================================
if (isset($_POST['finalizar_pedido'])) {
    if (!empty($_SESSION['carrinho'])) {
        // Por enquanto, mantém salvando os pedidos realizados na sessão temporária
        $_SESSION['pedido_realizados'][] = [
            'usuario' => $_SESSION['usuario'],
            'itens' => $_SESSION['carrinho'],
            'total' => $_POST['total_pedido'],
            'data' => date('d/m/Y H:i'),
            'status' => 'Pendente'
        ];
        
        // Limpa o carrinho após finalizar o pedido
        $_SESSION['carrinho'] = [];
        
        // Redireciona para a home com uma mensagem de sucesso (ou para uma página de status)
        header("Location: ../user/home.php?sucesso=pedido_realizado");
        exit();
    }
}
?>