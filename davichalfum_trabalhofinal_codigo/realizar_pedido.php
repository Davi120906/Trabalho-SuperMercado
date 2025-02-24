<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario = $_SESSION['usuario'];
$nivel_acesso = $usuario['nivel_acesso'];

if ($nivel_acesso !== 'administrador' && $nivel_acesso !== 'gerente' && $nivel_acesso !== 'usuario') {
    header("Location: dashboard.php");
    exit();
}

// Consulta todos os produtos cadastrados no banco de dados
$sql = "SELECT * FROM produtos";
$stmt = $conn->prepare($sql);
$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processa os produtos escolhidos para o pedido e salva na sessão
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Salva os produtos escolhidos na sessão
    $produtos_selecionados = [];
    foreach ($_POST['produtos'] as $produto_id => $quantidade) {
        if ($quantidade > 0) {
            $produtos_selecionados[] = ['produto_id' => $produto_id, 'quantidade' => $quantidade];
        }
    }

    // Armazena os produtos selecionados na sessão
    $_SESSION['pedido'] = $produtos_selecionados;

    // Redireciona para a página de confirmação
    header("Location: confirmar_pedido.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realizar Pedido</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Realizar Pedido</h1>
        <a href="dashboard.php">Voltar ao Dashboard</a>
    </header>

    <main>
        <form action="realizar_pedido.php" method="POST">
            <h2>Selecione os Produtos</h2>
            <table>
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Preço</th>
                        <th>Quantidade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $produto): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                            <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                            <td>
                                <input type="number" name="produtos[<?php echo $produto['id']; ?>]" value="0" min="0" max="<?php echo $produto['quantidade']; ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit">Confirmar Pedido</button>
        </form>
    </main>
</body>
</html>
