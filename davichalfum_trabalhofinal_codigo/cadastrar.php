<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $nivel_acesso = $_POST['nivel_acesso'];

    try {
        // Verifica se o e-mail já está cadastrado
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $erro = "E-mail já cadastrado!";
        } else {
            // Insere o novo usuário no banco de dados
            $stmt = $conn->prepare("
                INSERT INTO usuarios (nome, email, senha, nivel_acesso)
                VALUES (:nome, :email, :senha, :nivel_acesso)
            ");
            $stmt->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':senha' => hash('sha256', $senha),
                ':nivel_acesso' => $nivel_acesso,
            ]);

            // Recupera o ID do novo usuário
            $usuarioId = $conn->lastInsertId();

            // Inicia a sessão para o novo usuário
            $_SESSION['usuario'] = [
                'id' => $usuarioId,
                'nome' => $nome,
                'nivel_acesso' => $nivel_acesso,
            ];

            // Insere o login na tabela 'login'
            $stmt = $conn->prepare("INSERT INTO login (id, date, user) VALUES (:id, NOW(), :user)");
            $stmt->execute([
                ':id' => $usuarioId,
                ':user' => $nome // Insere o nome do usuário que está se cadastrando
            ]);

            // Redireciona para o dashboard após o cadastro
            header("Location: dashboard.php");
            exit();
        }
    } catch (PDOException $e) {
        $erro = "Erro no sistema: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar-se</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Cadastro</h1>
    </header>
    <main>
        <h2>Crie sua conta</h2>
        <?php if (isset($erro)): ?>
            <p style="color: red;"><?= $erro ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" required>
            <br>
            <label for="email">E-mail:</label>
            <input type="email" name="email" id="email" required>
            <br>
            <label for="senha">Senha:</label>
            <input type="password" name="senha" id="senha" required>
            <br>
            <label for="nivel_acesso">Nível de Acesso:</label>
            <select name="nivel_acesso" id="nivel_acesso" required>
                <option value="usuario">Usuário</option>
                <option value="gerente">Gerente</option>
                <option value="administrador">Administrador</option>
            </select>
            <br>
            <button type="submit">Cadastrar</button>
        </form>
    </main>
</body>
</html>
