<?php
session_start();
require_once 'conexao.php';

// Inicializa as variáveis de filtros
$marca = isset($_POST['marca']) ? $_POST['marca'] : '';
$quantidade_maior = isset($_POST['quantidade_maior']) ? $_POST['quantidade_maior'] : '';
$quantidade_menor = isset($_POST['quantidade_menor']) ? $_POST['quantidade_menor'] : '';
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
$produtos = [];

// Monta a consulta com base nos filtros fornecidos
try {
    $query = "SELECT * FROM produtos WHERE 1=1"; // 1=1 é uma condição verdadeira para facilitar a construção da query dinâmica
    
    // Adiciona o filtro de marca
    if ($marca) {
        $query .= " AND marca = :marca";
    }

    // Adiciona o filtro de intervalo de quantidade
    if ($quantidade_maior) {
        $query .= " AND quantidade >= :quantidade_maior";
    }
    if ($quantidade_menor) {
        $query .= " AND quantidade <= :quantidade_menor";
    }

    // Adiciona o filtro de tipo
    if ($tipo) {
        $query .= " AND tipo = :tipo";
    }

    $stmt = $conn->prepare($query);

    // Vincula os parâmetros aos filtros
    if ($marca) {
        $stmt->bindParam(':marca', $marca);
    }
    if ($quantidade_maior) {
        $stmt->bindParam(':quantidade_maior', $quantidade_maior, PDO::PARAM_INT);
    }
    if ($quantidade_menor) {
        $stmt->bindParam(':quantidade_menor', $quantidade_menor, PDO::PARAM_INT);
    }
    if ($tipo) {
        $stmt->bindParam(':tipo', $tipo);
    }

    // Executa a consulta
    $stmt->execute();

    // Obtém os produtos
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $erro = "Erro no sistema: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Produtos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Consulta de Produtos</h1>
    </header>
    
    <main>
        <h2>Filtrar Produtos</h2>
        
        <form method="POST">
            <label for="marca">Marca:</label>
            <input type="text" name="marca" id="marca" value="<?= htmlspecialchars($marca) ?>">
            <br>

            <label for="quantidade_maior">Quantidade maior que:</label>
            <input type="number" name="quantidade_maior" id="quantidade_maior" value="<?= htmlspecialchars($quantidade_maior) ?>" min="1">
            <br>

            <label for="quantidade_menor">Quantidade menor que:</label>
            <input type="number" name="quantidade_menor" id="quantidade_menor" value="<?= htmlspecialchars($quantidade_menor) ?>" min="1">
            <br>

            <label for="tipo">Tipo:</label>
            <input type="text" name="tipo" id="tipo" value="<?= htmlspecialchars($tipo) ?>">
            <br>

            <button type="submit">Consultar</button>
        </form>

        <?php if (isset($erro)): ?>
            <p style="color: red;"><?= $erro ?></p>
        <?php endif; ?>

        <?php if (count($produtos) > 0): ?>
            <h3>Produtos encontrados:</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Marca</th>
                        <th>Quantidade</th>
                        <th>Tipo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $produto): ?>
                        <tr>
                            <td><?= $produto['id'] ?></td>
                            <td><?= htmlspecialchars($produto['nome']) ?></td>
                            <td><?= htmlspecialchars($produto['marca']) ?></td>
                            <td><?= $produto['quantidade'] ?></td>
                            <td><?= htmlspecialchars($produto['tipo']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum produto encontrado com os filtros selecionados.</p>
        <?php endif; ?>
    </main>
</body>
</html>
