<?php
declare(strict_types = 1);
session_start();

class Product {
    public int $id;
    public function __construct(
        public string $name,
        public string $description,
        public float $price,
        public int $storage
    ) {}
}

if (!isset($_SESSION['listProducts'])) {
    $_SESSION['listProducts'] = [];
}

$message = "";

function addProduct(Product $product) {
    $lastProduct = end($_SESSION['listProducts']);
    $product->id = ($lastProduct === false) ? 1 : $lastProduct->id + 1;
    $_SESSION['listProducts'][] = $product;
    return $product;
}

function updateProduct(Product $productRequest, int $id) {
    $found = false;
    foreach ($_SESSION['listProducts'] as $product) {
        if ($product->id == $id) {
            if (!empty($productRequest->name)) $product->name = $productRequest->name;
            if (!empty($productRequest->description)) $product->description = $productRequest->description;
            if ($productRequest->price > 0) $product->price = $productRequest->price;
            if ($productRequest->storage >= 0) $product->storage = $productRequest->storage;
            $found = true;
            break;
        }
    }
    return $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'create') {
        if (empty($_POST['product_name']) || empty($_POST['product_description']) || empty($_POST['product_price'])) {
            $message = "<div class='alert error'>Erro: Preencha os campos obrigatórios para cadastro!</div>";
        } else {
            $product = new Product(
                $_POST['product_name'],
                $_POST['product_description'],
                (float) str_replace(',', '.', $_POST['product_price']),
                (int) $_POST['product_storage']
            );
            addProduct($product);
            $message = "<div class='alert success'>Produto cadastrado!</div>";
        }
    } elseif ($action === 'update') {
        $id = (int) $_POST['product_id'];
        $productReq = new Product(
            $_POST['product_name'] ?? '',
            $_POST['product_description'] ?? '',
            (float) str_replace(',', '.', ($_POST['product_price'] ?? '0')),
            (int) ($_POST['product_storage'] ?? 0)
        );
        if (updateProduct($productReq, $id)) {
            $message = "<div class='alert success'>Produto #$id atualizado!</div>";
        } else {
            $message = "<div class='alert error'>Produto #$id não encontrado!</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciador de Estoque</title>
    <style>
        :root {
            --primary: #4f46e5;
            --secondary: #0ea5e9;
            --bg: #f1f5f9;
            --card: #ffffff;
            --text: #334155;
            --border: #e2e8f0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background: var(--bg); color: var(--text); padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .grid { display: grid; grid-template-columns: 350px 1fr; gap: 20px; }
        .card { background: var(--card); padding: 20px; border-radius: 8px; border: 1px solid var(--border); height: fit-content; }
        .input-group { margin-bottom: 15px; }
        label { display: block; font-size: 12px; font-weight: bold; margin-bottom: 5px; text-transform: uppercase; color: #64748b; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 4px; }
        .btn-primary { width: 100%; padding: 12px; background: var(--primary); color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-update { background: var(--secondary); margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; background: #f8fafc; border-bottom: 2px solid var(--border); }
        td { padding: 12px; border-bottom: 1px solid var(--border); }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; }
        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
        .id-badge { background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-size: 11px; }
    </style>
</head>
<body>

<div class="container">
    <?= $message ?>
    <div class="grid">
        <aside class="card">
            <h2>Gerenciar</h2>
            <form method="POST">
                <input type="hidden" name="action" id="form_action" value="create">
                <div class="input-group">
                    <label>ID do Produto (Apenas para atualizar)</label>
                    <input type="number" name="product_id">
                </div>
                <div class="input-group">
                    <label>Nome</label>
                    <input type="text" name="product_name">
                </div>
                <div class="input-group">
                    <label>Descrição</label>
                    <textarea name="product_description"></textarea>
                </div>
                <div class="input-group">
                    <label>Preço</label>
                    <input type="text" name="product_price">
                </div>
                <div class="input-group">
                    <label>Estoque</label>
                    <input type="number" name="product_storage">
                </div>
                <button type="submit" name="action" value="create" class="btn-primary">Cadastrar</button>
                <button type="submit" name="action" value="update" class="btn-primary btn-update">Atualizar por ID</button>
            </form>
        </aside>

        <main class="card">
            <h2>Lista de Produtos</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produto</th>
                        <th>Descrição</th>
                        <th>Preço</th>
                        <th>Estoque</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['listProducts'] as $p): ?>
                    <tr>
                        <td><span class="id-badge">#<?= $p->id ?></span></td>
                        <td><strong><?= htmlspecialchars($p->name) ?></strong></td>
                        <td><?= htmlspecialchars($p->description) ?></td>
                        <td>R$ <?= number_format($p->price, 2, ',', '.') ?></td>
                        <td><?= $p->storage ?> un</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</div>

</body>
</html>