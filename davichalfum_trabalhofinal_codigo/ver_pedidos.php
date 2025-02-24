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

// Consulta os pedidos do usuário logado
$sql = "SELECT * FROM pedidos WHERE id_usuario = :usuario_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':usuario_id', $usuario_id);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Meus Pedidos</h1>
        <a href="dashboard.php">Voltar ao Dashboard</a>
    </header>

    <main>
        <h2>Lista de Meus Pedidos</h2>
        <?php if (count($pedidos) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID do Pedido</th>
                        <th>Status</th>
                        <th>Data do Pedido</th>
                        <th>Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?php echo $pedido['id']; ?></td>
                            <td><?php echo $pedido['status']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></td>
                            <td>
                                <a href="detalhes_pedido.php?id=<?php echo $pedido['id']; ?>">Ver Detalhes</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Você ainda não fez nenhum pedido.</p>
        <?php endif; ?>
    </main>
</body>
</html>
