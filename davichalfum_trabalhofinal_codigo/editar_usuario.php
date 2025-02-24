<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário está logado e tem permissão para editar
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario = $_SESSION['usuario'];
$nivel_acesso = $usuario['nivel_acesso'];

if ($nivel_acesso !== 'administrador') {  // Apenas administradores podem editar usuários
    header("Location: dashboard.php");
    exit();
}

// Verifica se o id foi passado na URL
if (!isset($_GET['id'])) {
    header("Location: gerenciar_usuarios.php");
    exit();
}

$id = $_GET['id'];

// Consulta o usuário para editar
$sql = "SELECT * FROM usuarios WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifica se o usuário foi encontrado
if (!$usuario) {
    echo "Usuário não encontrado!";
    exit();
}

// Processa o formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Editar Usuário</h1>
        <a href="gerenciar_usuarios.php">Voltar ao Gerenciamento de Usuários</a>
    </header>

    <main>
        <form action="editar_usuario.php?id=<?php echo $usuario['id']; ?>" method="POST">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>

            <label for="nivel_acesso">Nível de Acesso:</label>
            <select name="nivel_acesso" id="nivel_acesso" required>
                <option value="administrador" <?php echo ($usuario['nivel_acesso'] == 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                <option value="gerente" <?php echo ($usuario['nivel_acesso'] == 'gerente') ? 'selected' : ''; ?>>Gerente</option>
                <option value="usuario" <?php echo ($usuario['nivel_acesso'] == 'usuario') ? 'selected' : ''; ?>>Usuário</option>
            </select>

            <button type="submit">Salvar Alterações</button>
        </form>
    </main>
</body>
</html>
