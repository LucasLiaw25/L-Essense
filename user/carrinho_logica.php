<?php
// dashboard/carrinho_logica.php
declare(strict_types=1);

require_once __DIR__ . '/../auth/VerificarLogin.php';
require_once __DIR__ . '/../auth/Conexao.php'; 

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Adicionar Item
if (isset($_GET['adicionar'])) {
    $id = (int)$_GET['adicionar'];
    
    $sql_prod = "SELECT nome, preco, estoque FROM produtos WHERE id = ?";
    $stmt_prod = mysqli_prepare($conexao, $sql_prod);
    mysqli_stmt_bind_param($stmt_prod, "i", $id);
    mysqli_stmt_execute($stmt_prod);
    $resultado = mysqli_stmt_get_result($stmt_prod);
    $produto = mysqli_fetch_assoc($resultado);

    if ($produto) {
        $estoqueDisponivel = (int)$produto['estoque'];
        $quantidadeNoCarrinho = isset($_SESSION['carrinho'][$id]) ? $_SESSION['carrinho'][$id]['quantidade'] : 0;

        if ($quantidadeNoCarrinho < $estoqueDisponivel) {
            if (isset($_SESSION['carrinho'][$id])) {
                $_SESSION['carrinho'][$id]['quantidade']++;
            } else {
                $_SESSION['carrinho'][$id] = [
                    'nome' => $produto['nome'],
                    'preco' => (float)$produto['preco'],
                    'quantidade' => 1
                ];
            }
            header("Location: ../user/home.php?sucesso=adicionado");
        } else {
            header("Location: ../user/home.php?erro=estoque_insuficiente");
        }
    }
    exit();
}

// Remover Item
if (isset($_GET['remover'])) {
    $id = (int)$_GET['remover'];
    if (isset($_SESSION['carrinho'][$id])) {
        $_SESSION['carrinho'][$id]['quantidade']--;
        if ($_SESSION['carrinho'][$id]['quantidade'] <= 0) {
            unset($_SESSION['carrinho'][$id]);
        }
    }
    header("Location: ../user/home.php");
    exit();
}

// Finalizar Pedido Real no Banco de Dados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_pedido'])) {
    if (empty($_SESSION['carrinho'])) {
        header("Location: ../user/home.php?erro=carrinho_vazio");
        exit();
    }

    $usuario_id = $_SESSION['usuario_id'] ?? null;
    $usuario_nome = $_SESSION['usuario'] ?? null;
    $total_pedido = (float)($_POST['total_pedido'] ?? 0);

    if (!$usuario_id) {
        header("Location: ../user/login.php?erro=sessao_expirada");
        exit();
    }

    mysqli_begin_transaction($conexao);

    try {
        // Valida estoque preventivamente
        foreach ($_SESSION['carrinho'] as $id_prod => $item) {
            $sql_check = "SELECT estoque FROM produtos WHERE id = ?";
            $stmt_check = mysqli_prepare($conexao, $sql_check);
            mysqli_stmt_bind_param($stmt_check, "i", $id_prod);
            mysqli_stmt_execute($stmt_check);
            $res_check = mysqli_stmt_get_result($stmt_check);
            $prod_check = mysqli_fetch_assoc($res_check);
            mysqli_stmt_close($stmt_check);

            if (!$prod_check || (int)$item['quantidade'] > (int)$prod_check['estoque']) {
                throw new Exception("Estoque insuficiente.");
            }
        }

        // Insere Pedido Pai
        $sql_pedido = "INSERT INTO pedidos (usuario_id, usuario_nome, total, status) VALUES (?, ?, ?, 'Pendente')";
        $stmt_ped = mysqli_prepare($conexao, $sql_pedido);
        mysqli_stmt_bind_param($stmt_ped, "isd", $usuario_id, $usuario_nome, $total_pedido);
        mysqli_stmt_execute($stmt_ped);
        $pedido_id = mysqli_insert_id($conexao);
        mysqli_stmt_close($stmt_ped);

        // Insere Itens Filhos e abate o estoque
        foreach ($_SESSION['carrinho'] as $id_prod => $item) {
            $sql_item = "INSERT INTO pedido_itens (pedido_id, produto_id, nome, preco, quantidade) VALUES (?, ?, ?, ?, ?)";
            $stmt_item = mysqli_prepare($conexao, $sql_item);
            mysqli_stmt_bind_param($stmt_item, "iisdi", $pedido_id, $id_prod, $item['nome'], $item['preco'], $item['quantidade']);
            mysqli_stmt_execute($stmt_item);
            mysqli_stmt_close($stmt_item);

            $sql_baixa = "UPDATE produtos SET estoque = estoque - ? WHERE id = ?";
            $stmt_baixa = mysqli_prepare($conexao, $sql_baixa);
            mysqli_stmt_bind_param($stmt_baixa, "ii", $item['quantidade'], $id_prod);
            mysqli_stmt_execute($stmt_baixa);
            mysqli_stmt_close($stmt_baixa);
        }

        mysqli_commit($conexao);
        $_SESSION['carrinho'] = [];
        header("Location: ../user/home.php?sucesso=pedido_realizado");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conexao);
        header("Location: ../user/home.php?erro=falha_salvar_pedido");
        exit();
    }
}
?>