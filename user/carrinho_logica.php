<?php
// dashboard/carrinho_logica.php
declare(strict_types=1);

require_once __DIR__ . '/../auth/VerificarLogin.php';
require_once __DIR__ . '/../auth/Conexao.php'; // Garante o acesso à variável $conexao

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// ==========================================
// LÓGICA PARA ADICIONAR AO CARRINHO
// ==========================================
if (isset($_GET['adicionar'])) {
    $id = (int)$_GET['adicionar'];
    
    // Consulta o produto diretamente no Banco de Dados para ver o estoque real atualizado
    $sql_prod = "SELECT nome, preco, estoque FROM produtos WHERE id = ?";
    $stmt_prod = mysqli_prepare($conexao, $sql_prod);
    mysqli_stmt_bind_param($stmt_prod, "i", $id);
    mysqli_stmt_execute($stmt_prod);
    $resultado = mysqli_stmt_get_result($stmt_prod);
    $produto = mysqli_fetch_assoc($resultado);

    if ($produto) {
        $estoqueDisponivel = (int)$produto['estoque'];
        // Pega a quantidade que já está no carrinho (se não houver, é 0)
        $quantidadeNoCarrinho = isset($_SESSION['carrinho'][$id]) ? $_SESSION['carrinho'][$id]['quantidade'] : 0;

        // 1ª VERIFICAÇÃO: Verifica se o que já tem + 1 ultrapassa o estoque do Banco
        if (($quantidadeNoCarrinho + 1) > $estoqueDisponivel) {
            header("Location: ../user/home.php?erro=sem_estoque");
            exit();
        }

        // Adiciona ou incrementa no carrinho armazenando os dados vindos do Banco
        if (isset($_SESSION['carrinho'][$id])) {
            $_SESSION['carrinho'][$id]['quantidade']++;
        } {
            $_SESSION['carrinho'][$id] = [
                'nome' => $produto['nome'],
                'preco' => (float)$produto['preco'],
                'quantidade' => 1
            ];
        }
        header("Location: ../user/home.php?sucesso=adicionado");
        exit();
    } else {
        header("Location: ../user/home.php?erro=produto_nao_encontrado");
        exit();
    }
}

// ==========================================
// LÓGICA PARA FINALIZAR O PEDIDO (LANÇAR)
// ==========================================
if (isset($_POST['finalizar_pedido'])) {
    if (!empty($_SESSION['carrinho'])) {
        
        // 2ª VERIFICAÇÃO (CRÍTICA): Valida o estoque de todos os itens antes de fechar o pedido
        foreach ($_SESSION['carrinho'] as $id_prod => $item) {
            $sql_check = "SELECT nome, estoque FROM produtos WHERE id = ?";
            $stmt_check = mysqli_prepare($conexao, $sql_check);
            mysqli_stmt_bind_param($stmt_check, "i", $id_prod);
            mysqli_stmt_execute($stmt_check);
            $res_check = mysqli_stmt_get_result($stmt_check);
            $prod_check = mysqli_fetch_assoc($res_check);

            if (!$prod_check || (int)$item['quantidade'] > (int)$prod_check['estoque']) {
                // Se algum item acabou enquanto o cliente navegava, bloqueia e avisa
                header("Location: ../user/home.php?erro=estoque_insuficiente_finalizar");
                exit();
            }
        }

        // Se todos os produtos passarem no teste de estoque, procede à gravação do pedido
        // [Aqui entra o seu código de INSERT na tabela de pedidos/histórico caso exista]
        
        // Exemplo de baixa de estoque automática no Banco após aprovação do pedido:
        foreach ($_SESSION['carrinho'] as $id_prod => $item) {
            $sql_baixa = "UPDATE produtos SET estoque = estoque - ? WHERE id = ?";
            $stmt_baixa = mysqli_prepare($conexao, $sql_baixa);
            mysqli_stmt_bind_param($stmt_baixa, "ii", $item['quantidade'], $id_prod);
            mysqli_stmt_execute($stmt_baixa);
        }

        $_SESSION['pedido_realizados'][] = [
            'usuario' => $_SESSION['usuario'],
            'itens' => $_SESSION['carrinho'],
            'total' => $_POST['total_pedido'],
            'data' => date('d/m/Y H:i'),
            'status' => 'Pendente'
        ];

        // Limpa o carrinho após o sucesso
        $_SESSION['carrinho'] = [];
        header("Location: ../user/home.php?sucesso=pedido_realizado");
        exit();
    }
    header("Location: ../user/home.php");
    exit();
}