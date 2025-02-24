<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário está logado e tem permissão para gerenciar usuários
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario = $_SESSION['usuario'];
$nivel_acesso = $usuario['nivel_acesso'];

if ($nivel_acesso !== 'administrador') {  // Apenas administradores podem gerenciar usuários
    header("Location: dashboard.php");
    exit();
}

// Consulta todos os usuários cadastrados no banco de dados
$sql = "SELECT * FROM usuarios";
$stmt = $conn->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Função para excluir um usuário
if (isset($_GET['excluir_id'])) {
    $excluir_id = $_GET['excluir_id'];
    try {
        $sql_delete = "DELETE FROM usuarios WHERE id = :id";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bindParam(':id', $excluir_id);
        $stmt_delete->execute();
        header("Location: gerenciar_usuarios.php");
    } catch (PDOException $e) {
        echo "Erro ao excluir o usuário: " . $e->getMessage();
    }
}

// Função para editar um usuário
if (isset($_POST['editar_usuario'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $nivel_acesso = $_POST['nivel_acesso'];
    
    try {
        $sql_update = "UPDATE usuarios SET nome = :nome, email = :email, nivel_acesso = :nivel_acesso WHERE id = :id";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bindParam(':id', $id);
        $stmt_update->bindParam(':nome', $nome);
        $stmt_update->bindParam(':email', $email);
        $stmt_update->bindParam(':nivel_acesso', $nivel_acesso);
        $stmt_update->execute();
        header("Location: gerenciar_usuarios.php");
    } catch (PDOException $e) {
        echo "Erro ao editar o usuário: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Gerenciar Usuários</h1>
        <a href="dashboard.php">Voltar ao Dashboard</a>
    </header>

    <main>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Nível de Acesso</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['nivel_acesso']); ?></td>
                    <td>
                        <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>">Editar</a> |
                        <a href="gerenciar_usuarios.php?excluir_id=<?php echo $usuario['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir este usuário?');">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
