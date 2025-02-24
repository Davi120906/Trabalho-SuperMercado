<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Verifica se a ação foi para atualizar o status de entrega
if (isset($_GET['acao']) && $_GET['acao'] == 'concluir' && isset($_GET['id'])) {
    $entrega_id = $_GET['id'];

    // Inicia a transação
    $conn->beginTransaction();
    
    try {
        // Atualiza o status da entrega para "Concluída" e define a data de entrega como a data atual
        $sql_entrega = "UPDATE entregas SET status = 'Concluída', data_entrega = NOW() WHERE id = :id";
        $stmt_entrega = $conn->prepare($sql_entrega);
        $stmt_entrega->bindParam(':id', $entrega_id);
        $stmt_entrega->execute();

        // Atualiza o status do pedido para "Concluído"
        $sql_pedido = "UPDATE pedidos SET status = 'Concluído' WHERE id = (SELECT pedido_id FROM entregas WHERE id = :entrega_id)";
        $stmt_pedido = $conn->prepare($sql_pedido);
        $stmt_pedido->bindParam(':entrega_id', $entrega_id);
        $stmt_pedido->execute();

        // Commit da transação
        $conn->commit();

        header("Location: gerenciar_entregas.php");
        exit();
    } catch (Exception $e) {
        // Caso algo dê errado, faz rollback
        $conn->rollBack();
        echo "Erro ao concluir entrega: " . $e->getMessage();
        exit();
    }
}

// Verifica se a ação foi para excluir uma entrega
if (isset($_GET['acao']) && $_GET['acao'] == 'excluir' && isset($_GET['id'])) {
    $entrega_id = $_GET['id'];

    // Exclui a entrega
    $sql = "DELETE FROM entregas WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $entrega_id);
    $stmt->execute();
    header("Location: gerenciar_entregas.php");
    exit();
}

// Consulta todas as entregas
$sql = "SELECT * FROM entregas";
$stmt = $conn->prepare($sql);
$stmt->execute();
$entregas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Entregas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Gerenciar Entregas</h1>
        <a href="dashboard.php">Voltar ao Dashboard</a>
    </header>

    <main>
        <h2>Lista de Entregas</h2>
        <table>
            <thead>
                <tr>
                    <th>ID do Pedido</th>
                    <th>Status</th>
                    <th>Data de Entrega</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entregas as $entrega): ?>
                    <tr>
                        <td><?php echo $entrega['pedido_id']; ?></td>
                        <td><?php echo $entrega['status']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($entrega['data_entrega'])); ?></td>
                        <td>
                            <?php if ($entrega['status'] != 'Concluída'): ?>
                                <a href="gerenciar_entregas.php?acao=concluir&id=<?php echo $entrega['id']; ?>">Marcar como Concluída</a> |
                            <?php endif; ?>
                            <a href="gerenciar_entregas.php?acao=excluir&id=<?php echo $entrega['id']; ?>" onclick="return confirm('Tem certeza de que deseja excluir esta entrega?')">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
