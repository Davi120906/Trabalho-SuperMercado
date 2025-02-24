<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário está logado e tem permissões para acessar essa página
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario = $_SESSION['usuario'];
$nivel_acesso = $usuario['nivel_acesso'];

// Permite apenas o acesso para administradores ou gerentes
if ($nivel_acesso !== 'administrador' && $nivel_acesso !== 'gerente') {
    header("Location: dashboard.php");
    exit();
}

// Excluir um pedido
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['excluir_pedido'])) {
    $pedido_id = $_POST['pedido_id'];

    try {
        // Inicia uma transação para garantir a integridade dos dados
        $conn->beginTransaction();

        // Exclui os itens do pedido da tabela pedido_itens
        $sql = "DELETE FROM pedido_itens WHERE pedido_id = :pedido_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':pedido_id', $pedido_id);
        $stmt->execute();

        // Exclui o pedido da tabela pedidos
        $sql = "DELETE FROM pedidos WHERE id = :pedido_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':pedido_id', $pedido_id);
        $stmt->execute();

        // Confirma a transação
        $conn->commit();

        $_SESSION['sucesso'] = "Pedido excluído com sucesso!";
        
    } catch (Exception $e) {
        // Caso haja erro, faz o rollback da transação
        $conn->rollBack();
        $_SESSION['erro'] = "Erro ao excluir o pedido: " . $e->getMessage();
    }

    // Redireciona para a mesma página para exibir a mensagem
    header("Location: gerenciar_pedidos.php");
    exit();
}

// Atualizar o status do pedido
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['atualizar_status'])) {
    $pedido_id = $_POST['pedido_id'];
    $status = $_POST['status'];

    $sql = "UPDATE pedidos SET status = :status WHERE id = :pedido_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':pedido_id', $pedido_id);
    $stmt->execute();

    $_SESSION['sucesso'] = "Status atualizado com sucesso!";
    header("Location: gerenciar_pedidos.php");
    exit();
}

// Consulta todos os pedidos no banco de dados
$sql = "SELECT * FROM pedidos";
$stmt = $conn->prepare($sql);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Pedidos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Gerenciar Pedidos</h1>
        <a href="dashboard.php">Voltar ao Dashboard</a>
    </header>

    <main>
        <h2>Pedidos Realizados</h2>
        
        <!-- Exibe mensagens de sucesso -->
        <?php if (isset($_SESSION['sucesso'])): ?>
            <div class="alerta sucesso"><?php echo $_SESSION['sucesso']; unset($_SESSION['sucesso']); ?></div>
        <?php endif; ?>

        <!-- Exibe mensagens de erro -->
        <?php if (isset($_SESSION['erro'])): ?>
            <div class="alerta erro"><?php echo $_SESSION['erro']; unset($_SESSION['erro']); ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID do Pedido</th>
                    <th>Usuário</th>
                    <th>Data do Pedido</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td><?php echo $pedido['id']; ?></td>
                        <td>
                            <?php
                                // Consulta o nome do usuário associado ao pedido
                                $sql = "SELECT nome FROM usuarios WHERE id = :id_usuario";
                                $stmt = $conn->prepare($sql);
                                $stmt->bindParam(':id_usuario', $pedido['id_usuario']);
                                $stmt->execute();
                                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                                echo $usuario['nome'];
                            ?>
                        </td>
                        <td><?php echo date("d/m/Y H:i", strtotime($pedido['data_pedido'])); ?></td>
                        <td><?php echo $pedido['status']; ?></td>
                        <td>
                            <form action="gerenciar_pedidos.php" method="POST">
                                <select name="status" required>
                                    <option value="Em Andamento" <?php echo $pedido['status'] === 'Em Andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                    <option value="Finalizado" <?php echo $pedido['status'] === 'Finalizado' ? 'selected' : ''; ?>>Finalizado</option>
                                    <option value="Cancelado" <?php echo $pedido['status'] === 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                                <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                <button type="submit" name="atualizar_status">Atualizar Status</button>
                            </form>

                            <!-- Formulário para excluir o pedido -->
                            <form action="gerenciar_pedidos.php" method="POST" style="margin-top: 10px;">
                                <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                <button type="submit" name="excluir_pedido" onclick="return confirm('Tem certeza que deseja excluir este pedido?')">Excluir Pedido</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
