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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        empty($_POST['product_name']) || 
        empty($_POST['product_description']) || 
        empty($_POST['product_price']) || 
        !isset($_POST['product_storage'])
    ) {
        $message = "<div class='alert error'>Erro: Todos os campos devem estar preenchidos!</div>";
    } else {
        $name = $_POST['product_name'];
        $description = $_POST['product_description'];
        $price = (float) str_replace(',', '.', $_POST['product_price']);
        $storage = (int) $_POST['product_storage'];

        $product = new Product($name, $description, $price, $storage);
        addProduct($product);
        $message = "<div class='alert success'>Produto cadastrado com sucesso!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Estoque</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
            --success: #22c55e;
            --error: #ef4444;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { background-color: var(--bg); color: var(--text); padding: 2rem; }
        .container { max-width: 1100px; margin: 0 auto; }
        header { margin-bottom: 2rem; text-align: center; }
        .grid { display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; }
        @media (max-width: 850px) { .grid { grid-template-columns: 1fr; } }
        .card {
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            border: 1px solid var(--border);
        }
        h2 { margin-bottom: 1.5rem; font-size: 1.25rem; color: var(--primary); }
        .input-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 600; }
        input, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 1rem;
            transition: border 0.2s;
        }
        input:focus { outline: none; border-color: var(--primary); }
        .row { display: flex; gap: 1rem; }
        .row .input-group { flex: 1; }
        .btn-primary {
            width: 100%;
            background: var(--primary);
            color: white;
            padding: 0.8rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-primary:hover { background: var(--primary-hover); }
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 0.5rem; }
        th { text-align: left; padding: 1rem; border-bottom: 2px solid var(--border); color: var(--text-light); font-size: 0.85rem; text-transform: uppercase; }
        td { padding: 1rem; border-bottom: 1px solid var(--border); font-size: 0.95rem; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; text-align: center; font-weight: 600; }
        .alert.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>📦 Sistema de Produtos</h1>
    </header>

    <?= $message ?>

    <div class="grid">
        <section class="card">
            <h2>Cadastrar Novo</h2>
            <form method="POST">
                <div class="input-group">
                    <label>Nome do Produto*</label>
                    <input type="text" name="product_name" required>
                </div>

                <div class="input-group">
                    <label>Descrição*</label>
                    <textarea name="product_description" rows="3" required></textarea>
                </div>

                <div class="row">
                    <div class="input-group">
                        <label>Preço (R$)*</label>
                        <input type="text" name="product_price" required placeholder="0.00">
                    </div>
                    <div class="input-group">
                        <label>Estoque*</label>
                        <input type="number" name="product_storage" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Adicionar Produto</button>
            </form>
        </section>

        <section class="card">
            <h2>Produtos Cadastrados</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($_SESSION['listProducts'])): ?>
                            <tr>
                                <td colspan="4" style="text-align:center; padding: 20px;">Nenhum produto cadastrado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($_SESSION['listProducts'] as $p): ?>
                                <tr>
                                    <td>#<?= $p->id ?></td>
                                    <td><strong><?= htmlspecialchars($p->name) ?></strong></td>
                                    <td>R$ <?= number_format($p->price, 2, ',', '.') ?></td>
                                    <td><?= $p->storage ?> un.</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

</body>
</html>