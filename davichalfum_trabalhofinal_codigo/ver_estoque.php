<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário está logado e tem permissão para visualizar o estoque
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario = $_SESSION['usuario'];
$nivel_acesso = $usuario['nivel_acesso'];

if ($nivel_acesso !== 'administrador' && $nivel_acesso !== 'gerente') {
    header("Location: dashboard.php");
    exit();
}

// Consulta todos os produtos cadastrados no banco de dados
$sql = "SELECT * FROM produtos";
$stmt = $conn->prepare($sql);
$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Estoque</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px; /* Aumenta o espaçamento entre as células */
            margin-top: 20px;
        }
        th, td {
            padding: 15px; /* Aumenta o espaçamento dentro das células */
            border: 1px solid #ddd; /* Adiciona bordas às células */
            text-align: left;
        }
        img {
            width: 200px;
            float: left;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Estoque de Produtos</h1>
        <a href="dashboard.php">Voltar ao Dashboard</a>
    </header>

    <main>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Marca</th>
                    <th>Preço</th>
                    <th>Quantidade</th>
                    <th>Imagem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                <tr>
                    <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                    <td><?php echo htmlspecialchars($produto['tipo']); ?></td>
                    <td><?php echo htmlspecialchars($produto['marca']); ?></td>
                    <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                    <td><?php echo $produto['quantidade']; ?></td>
                    <td>
                        <?php if ($produto['imagem']): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($produto['imagem']); ?>" alt="Imagem do produto">
                        <?php else: ?>
                            <p>Sem imagem</p>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
