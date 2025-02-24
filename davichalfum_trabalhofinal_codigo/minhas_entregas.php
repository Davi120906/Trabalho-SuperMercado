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

// Verifica se a ação foi para atualizar o status de entrega
if (isset($_GET['acao']) && $_GET['acao'] == 'concluir' && isset($_GET['id'])) {
    $entrega_id = $_GET['id'];

    // Inicia a transação
    $conn->beginTransaction();
    
    try {
        // Atualiza o status da entrega para "Concluída" e define a data de entrega como a data atual
        $sql_entrega = "UPDATE entregas SET status = 'Concluída', data_entrega = NOW() WHERE id = :id AND pedido_id IN (SELECT id FROM pedidos WHERE id_usuario = :usuario_id)";
        $stmt_entrega = $conn->prepare($sql_entrega);
        $stmt_entrega->bindParam(':id', $entrega_id);
        $stmt_entrega->bindParam(':usuario_id', $usuario_id);
        $stmt_entrega->execute();

        // Atualiza o status do pedido para "Concluído"
        $sql_pedido = "UPDATE pedidos SET status = 'Concluído' WHERE id = (SELECT pedido_id FROM entregas WHERE id = :entrega_id) AND id_usuario = :usuario_id";
        $stmt_pedido = $conn->prepare($sql_pedido);
        $stmt_pedido->bindParam(':entrega_id', $entrega_id);
        $stmt_pedido->bindParam(':usuario_id', $usuario_id);
        $stmt_pedido->execute();

        // Commit da transação
        $conn->commit();

        header("Location: minhas_entregas.php");
        exit();
    } catch (Exception $e) {
        // Caso algo dê errado, faz rollback
        $conn->rollBack();
        echo "Erro ao concluir entrega: " . $e->getMessage();
        exit();
    }
}

// Consulta todas as entregas do usuário logado
$sql = "SELECT * FROM entregas WHERE pedido_id IN (SELECT id FROM pedidos WHERE id_usuario = :usuario_id)";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':usuario_id', $usuario_id);
$stmt->execute();
$entregas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Entregas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Minhas Entregas</h1>
        <a href="dashboard.php">Voltar ao Dashboard</a>
    </header>

    <main>
        <h2>Lista de Minhas Entregas</h2>
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
                                <a href="minhas_entregas.php?acao=concluir&id=<?php echo $entrega['id']; ?>">Marcar como Concluída</a>
                            <?php else: ?>
                                Concluída
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
