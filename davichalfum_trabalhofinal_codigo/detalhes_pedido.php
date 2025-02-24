<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario = $_SESSION['usuario'];
$usuario_id = $usuario['id'];  // Obtém o ID do usuário logado

// Verifica se o ID do pedido foi passado na URL
if (isset($_GET['id'])) {
    $pedido_id = $_GET['id'];

    // Consulta os detalhes do pedido
    $sql = "SELECT p.id, p.status, p.data_pedido, pi.quantidade, pr.nome AS produto_nome, pr.preco 
            FROM pedidos p
            JOIN pedido_itens pi ON p.id = pi.pedido_id
            JOIN produtos pr ON pi.produto_id = pr.id
            WHERE p.id = :pedido_id AND p.id_usuario = :usuario_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':pedido_id', $pedido_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    $itens_pedido = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verifica se o pedido existe para o usuário logado
    if (empty($itens_pedido)) {
        echo "Pedido não encontrado ou não pertence a este usuário.";
        exit();
    }

    // Dados do pedido
    $pedido = $itens_pedido[0];  // O primeiro item contém as informações do pedido (status, data_pedido)
} else {
    echo "ID do pedido não fornecido.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Pedido</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Detalhes do Pedido</h1>
        <a href="ver_pedidos.php">Voltar aos Meus Pedidos</a>
    </header>

    <main>
        <h2>Pedido #<?php echo $pedido['id']; ?></h2>
        <p>Status: <?php echo $pedido['status']; ?></p>
        <p>Data do Pedido: <?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></p>
        
        <h3>Itens do Pedido</h3>
        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preço</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens_pedido as $item): ?>
                    <tr>
                        <td><?php echo $item['produto_nome']; ?></td>
                        <td><?php echo $item['quantidade']; ?></td>
                        <td>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($item['quantidade'] * $item['preco'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p><strong>Total do Pedido: R$ 
            <?php
            $total_pedido = 0;
            foreach ($itens_pedido as $item) {
                $total_pedido += $item['quantidade'] * $item['preco'];
            }
            echo number_format($total_pedido, 2, ',', '.');
            ?>
        </strong></p>
    </main>
</body>
</html>
