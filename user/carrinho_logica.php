<?php
// Definição da classe para o PHP entender os objetos da sessão
if (!class_exists('Product')) {
    class Product {
        public int $id;
        public function __construct(
            public string $name,
            public string $description,
            public float $price,
            public int $storage
        ) {}
    }
}

// Use VerificarLogin para que CLIENTES também possam comprar
require_once __DIR__ . '/../auth/VerificarLogin.php';

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// logica para adicionar
if (isset($_GET['adicionar'])) {
    $id = (int)$_GET['adicionar'];
    
    if (isset($_SESSION['listProducts'])) {
        foreach ($_SESSION['listProducts'] as $produto) {
            if ($produto->id === $id) {
                
                // 1. Pega a quantidade que já está no carrinho (se não tiver, é 0)
                $quantidadeNoCarrinho = isset($_SESSION['carrinho'][$id]) ? $_SESSION['carrinho'][$id]['quantidade'] : 0;

                // 2. Verifica se (o que já tem + 1) ultrapassa o estoque disponível
                if (($quantidadeNoCarrinho + 1) > $produto->storage) {
                    // Se ultrapassar, volta para a home com um aviso de erro
                    header("Location: ../user/home.php?erro=sem_estoque");
                    exit();
                }

                // 3. Se passou na verificação, adiciona ou incrementa
                if (isset($_SESSION['carrinho'][$id])) {
                    $_SESSION['carrinho'][$id]['quantidade']++;
                } else {
                    $_SESSION['carrinho'][$id] = [
                        'nome' => $produto->name,
                        'preco' => $produto->price,
                        'quantidade' => 1
                    ];
                }
                break;
            }
        }
    }
    header("Location: ../user/home.php?sucesso=adicionado");
    exit();
}

// Finalizar Pedido (Lançar)
if (isset($_POST['finalizar_pedido'])) {
    if (!empty($_SESSION['carrinho'])) {
        $_SESSION['pedido_realizados'][] = [
            'usuario' => $_SESSION['usuario'],
            'itens' => $_SESSION['carrinho'],
            'total' => $_POST['total_pedido'],
            'data' => date('d/m/Y H:i'),
            'status' => 'Pendente'
        ];
        $_SESSION['carrinho'] = []; 
        header("Location: home.php?sucesso=pedido_realizados");
        exit();
    }
}