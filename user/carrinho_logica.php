<?php
// user/carrinho_logica.php
declare(strict_types=1);

require_once __DIR__ . '/../auth/VerificarLogin.php';

global $conexao;

// CONFIGURAÇÃO: Tempo em segundos para o pedido sumir do carrinho por inatividade (Ex: 7200 segundos = 2 horas)
define('CARRINHO_EXPIRACAO', 7200); 

/**
 * Função para obter o carrinho salvo no Cookie
 */
function obterCarrinho(): array {
    if (isset($_COOKIE['carrinho_lessence'])) {
        $dados = json_decode($_COOKIE['carrinho_lessence'], true);
        if (is_array($dados)) {
            // Verifica se o tempo limite de inatividade expirou
            if (isset($dados['expira_em']) && time() > $dados['expira_em']) {
                limparCarrinhoCookie();
                return [];
            }
            return $dados['itens'] ?? [];
        }
    }
    return [];
}

/**
 * Função para salvar o estado atual do carrinho no Cookie
 */
function salvarCarrinhoCookie(array $itens): void {
    $dadosParaSalvar = [
        'expira_em' => time() + CARRINHO_EXPIRACAO, // Define/renova o tempo para sumir
        'itens' => $itens
    ];
    // Salva o cookie válido por 30 dias no navegador, mas a lógica interna valida a inatividade
    setcookie('carrinho_lessence', json_encode($dadosParaSalvar), time() + (86400 * 30), "/");
}

/**
 * Função para destruir o Cookie do carrinho
 */
function limparCarrinhoCookie(): void {
    setcookie('carrinho_lessence', '', time() - 3600, "/");
    if (isset($_COOKIE['carrinho_lessence'])) {
        unset($_COOKIE['carrinho_lessence']);
    }
}

// Carrega os itens do carrinho vindo do Cookie
$carrinho_itens = obterCarrinho();

// --- LÓGICA DE ADICIONAR ITEM ---
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
        $quantidadeNoCarrinho = isset($carrinho_itens[$id]) ? $carrinho_itens[$id]['quantidade'] : 0;

        if ($quantidadeNoCarrinho < $estoqueDisponivel) {
            if (isset($carrinho_itens[$id])) {
                $carrinho_itens[$id]['quantidade']++;
            } else {
                $carrinho_itens[$id] = [
                    'nome' => $produto['nome'],
                    'preco' => (float)$produto['preco'],
                    'quantidade' => 1
                ];
            }
            salvarCarrinhoCookie($carrinho_itens); // Grava a alteração e renova o tempo
        }
    }
    mysqli_stmt_close($stmt_prod);
    header("Location: home.php");
    exit();
}

// --- LÓGICA DE REMOVER ITEM ---
if (isset($_GET['remover'])) {
    $id = (int)$_GET['remover'];
    
    if (isset($carrinho_itens[$id])) {
        if ($carrinho_itens[$id]['quantidade'] > 1) {
            $carrinho_itens[$id]['quantidade']--;
        } else {
            unset($carrinho_itens[$id]);
        }
        salvarCarrinhoCookie($carrinho_itens); // Grava a alteração e renova o tempo
    }
    header("Location: home.php");
    exit();
}

// --- LÓGICA DE FINALIZAR PEDIDO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_pedido'])) {
    if (empty($carrinho_itens)) {
        header("Location: home.php?erro=carrinho_vazio");
        exit();
    }

    $usuario_id = $_SESSION['usuario_id'] ?? 1;
    $usuario_nome = $_SESSION['usuario'] ?? 'Cliente';
    $total_pedido = (float)$_POST['total_pedido'];

    mysqli_begin_transaction($conexao);

    try {
        $sql_ped = "INSERT INTO pedidos (usuario_id, usuario_nome, total, status) VALUES (?, ?, ?, 'Pendente')";
        $stmt_ped = mysqli_prepare($conexao, $sql_ped);
        mysqli_stmt_bind_param($stmt_ped, "isd", $usuario_id, $usuario_nome, $total_pedido);
        mysqli_stmt_execute($stmt_ped);
        $pedido_id = mysqli_insert_id($conexao);
        mysqli_stmt_close($stmt_ped);

        foreach ($carrinho_itens as $id_prod => $item) {
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
        
        // Pedido feito com sucesso, remove o carrinho dos Cookies
        limparCarrinhoCookie();

        header("Location: ../dashboard/status.php?sucesso=1");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conexao);
        header("Location: home.php?erro=falha_pedido");
        exit();
    }
}