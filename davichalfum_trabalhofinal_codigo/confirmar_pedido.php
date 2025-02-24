<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario = $_SESSION['usuario'];

// Verifica se há produtos na sessão
if (!isset($_SESSION['pedido']) || empty($_SESSION['pedido'])) {
    header("Location: realizar_pedido.php");
    exit();
}

// Exibe os produtos do pedido
$produtos_selecionados = $_SESSION['pedido'];
$produtos = [];

foreach ($produtos_selecionados as $item) {
    $sql = "SELECT * FROM produtos WHERE id = :produto_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':produto_id', $item['produto_id']);
    $stmt->execute();
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    $produto['quantidade'] = $item['quantidade'];
    $produtos[] = $produto;
}

// Confirma o pedido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Cria um novo pedido
    $sql = "INSERT INTO pedidos (id_usuario) VALUES (:id_usuario)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_usuario', $usuario['id']);
    $stmt->execute();

    // Obtém o ID do pedido recém-criado
    $pedido_id = $conn->lastInsertId();

    // Adiciona os itens ao pedido
    foreach ($produtos_selecionados as $item) {
        $sql = "INSERT INTO pedido_itens (pedido_id, produto_id, quantidade) VALUES (:pedido_id, :produto_id, :quantidade)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':pedido_id', $pedido_id);
        $stmt->bindParam(':produto_id', $item['produto_id']);
        $stmt->bindParam(':quantidade', $item['quantidade']);
        $stmt->execute();
        
        // Atualiza a quantidade do produto no estoque
        $sql = "UPDATE produtos SET quantidade = quantidade - :quantidade WHERE id = :produto_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':quantidade', $item['quantidade']);
        $stmt->bindParam(':produto_id', $item['produto_id']);
        $stmt->execute();
    }

    // Calcula a data de entrega para 1 semana a partir de agora
    $data_entrega = date('Y-m-d H:i:s', strtotime('+1 week'));

    // Cria a entrega relacionada ao pedido
    $sql = "INSERT INTO entregas (pedido_id, status, data_entrega) VALUES (:pedido_id, 'Pendente', :data_entrega)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':pedido_id', $pedido_id);
    $stmt->bindParam(':data_entrega', $data_entrega);
    $stmt->execute();

    // Limpa os dados do pedido na sessão
    unset($_SESSION['pedido']);

    // Redireciona para o Dashboard
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Pedido</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Confirmar Pedido</h1>
        <a href="dashboard.php">Voltar ao Dashboard</a>
    </header>

    <main>
        <h2>Revise seu Pedido</h2>
        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Preço</th>
                    <th>Quantidade</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                        <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                        <td><?php echo $produto['quantidade']; ?></td>
                        <td>R$ <?php echo number_format($produto['preco'] * $produto['quantidade'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <form action="confirmar_pedido.php" method="POST">
            <button type="submit">Confirmar Pedido</button>
        </form>
    </main>
</body>
</html>
