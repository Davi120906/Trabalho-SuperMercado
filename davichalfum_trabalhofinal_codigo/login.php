<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    try {
        // Verifica se o usuário existe
        $stmt = $conn->prepare("SELECT id, nome, senha, nivel_acesso FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        // Verifica se a senha está correta
        if ($usuario && hash('sha256', $senha) === $usuario['senha']) {
            // Inicia a sessão para o usuário
            $_SESSION['usuario'] = [
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'nivel_acesso' => $usuario['nivel_acesso'],
            ];

            // Registra o login na tabela "login" com o ID, data/hora e o nome do usuário
            $stmt = $conn->prepare("INSERT INTO login (id, date, user) VALUES (:id, NOW(), :user)");
            $stmt->execute([
                ':id' => $usuario['id'],
                ':user' => $usuario['nome']  // Nome do usuário que está logando
            ]);

            // Redireciona para o dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $erro = "E-mail ou senha inválidos!";
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
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Login</h1>
    </header>
    <main>
        <h2>Entre com sua conta</h2>
        <?php if (isset($erro)): ?>
            <p style="color: red;"><?= $erro ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="email">E-mail:</label>
            <input type="email" name="email" id="email" required>
            <br>
            <label for="senha">Senha:</label>
            <input type="password" name="senha" id="senha" required>
            <br>
            <button type="submit">Entrar</button>
        </form>
        <p>Não tem uma conta? <a href="cadastrar.php">Cadastre-se</a></p>
    </main>
</body>
</html>
