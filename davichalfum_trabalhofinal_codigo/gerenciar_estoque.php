<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário está logado e se tem permissões para acessar essa página
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

// Consulta todos os produtos no banco de dados
$sql = "SELECT * FROM produtos";
$stmt = $conn->prepare($sql);
$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Atualiza a quantidade de um produto no estoque
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['atualizar_estoque'])) {
    $produto_id = $_POST['produto_id'];
    $quantidade = $_POST['quantidade'];

    // Verifica se a quantidade inserida é válida
    if ($quantidade < 0) {
        $_SESSION['erro'] = "A quantidade não pode ser negativa.";
    } else {
        // Atualiza a quantidade do produto no estoque
        $sql = "UPDATE produtos SET quantidade = :quantidade WHERE id = :produto_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':quantidade', $quantidade);
        $stmt->bindParam(':produto_id', $produto_id);
        $stmt->execute();

        $_SESSION['sucesso'] = "Estoque atualizado com sucesso!";
    }

    // Redireciona para a mesma página para exibir a mensagem
    header("Location: gerenciar_estoque.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Estoque</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Gerenciar Estoque</h1>
        <a href="dashboard.php">Voltar ao Dashboard</a>
    </header>

    <main>
        <h2>Produtos no Estoque</h2>
        
        <!-- Exibe mensagens de sucesso ou erro -->
        <?php if (isset($_SESSION['erro'])): ?>
            <div class="alerta erro"><?php echo $_SESSION['erro']; unset($_SESSION['erro']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['sucesso'])): ?>
            <div class="alerta sucesso"><?php echo $_SESSION['sucesso']; unset($_SESSION['sucesso']); ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Preço</th>
                    <th>Quantidade em Estoque</th>
                    <th>Atualizar Quantidade</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                        <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                        <td><?php echo $produto['quantidade']; ?></td>
                        <td>
                            <form action="gerenciar_estoque.php" method="POST">
                                <input type="number" name="quantidade" value="<?php echo $produto['quantidade']; ?>" min="0" required>
                                <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">
                                <button type="submit" name="atualizar_estoque">Atualizar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
