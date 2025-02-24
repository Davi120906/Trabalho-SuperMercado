<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário está logado e se tem permissão para cadastrar produtos
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

// Processa o formulário de cadastro de produto
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Dados do produto
    $nome = $_POST['nome'];
    $tipo = $_POST['tipo'];
    $marca = $_POST['marca'];
    // Verifica e corrige a vírgula no campo de preço
    $preco = str_replace(',', '.', $_POST['preco']);
    $quantidade = $_POST['quantidade'];
    
    // Processa a imagem
    $imagem = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        // Lê o conteúdo do arquivo da imagem
        $imagem_tmp = $_FILES['imagem']['tmp_name'];
        $imagem_conteudo = file_get_contents($imagem_tmp);
        $imagem = $imagem_conteudo; // Armazena o conteúdo da imagem
    }

    // Insere o produto no banco de dados
    try {
        $sql = "INSERT INTO produtos (nome, tipo, marca, preco, quantidade, imagem) 
                VALUES (:nome, :tipo, :marca, :preco, :quantidade, :imagem)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':marca', $marca);
        $stmt->bindParam(':preco', $preco);
        $stmt->bindParam(':quantidade', $quantidade);
        $stmt->bindParam(':imagem', $imagem, PDO::PARAM_LOB); // Especifica que a imagem é um BLOB
        $stmt->execute();
        
        echo "<script>alert('Produto cadastrado com sucesso!');</script>";
    } catch (PDOException $e) {
        echo "Erro ao cadastrar o produto: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Produto</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Cadastrar Produto</h1>
        <a href="dashboard.php">Voltar ao Dashboard</a>
    </header>

    <main>
        <form action="cadastrar_produtos.php" method="POST" enctype="multipart/form-data">
            <label for="nome">Nome do Produto:</label>
            <input type="text" name="nome" id="nome" required>

            <label for="tipo">Tipo do Produto:</label>
            <input type="text" name="tipo" id="tipo" required>

            <label for="marca">Marca:</label>
            <input type="text" name="marca" id="marca">

            <label for="preco">Preço:</label>
            <input type="text" name="preco" id="preco" required>

            <label for="quantidade">Quantidade:</label>
            <input type="number" name="quantidade" id="quantidade" required>

            <label for="imagem">Imagem do Produto:</label>
            <input type="file" name="imagem" id="imagem" accept="image/*">

            <button type="submit">Cadastrar Produto</button>
        </form>
    </main>
</body>
</html>
