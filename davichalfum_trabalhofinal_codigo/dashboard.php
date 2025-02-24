<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtém os dados do usuário logado
$usuario = $_SESSION['usuario'];

// Verifica o nível de acesso
$nivel_acesso = $usuario['nivel_acesso'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Bem-vindo ao Dashboard, <?= $usuario['nome'] ?>!</h1>
        <a href="logout.php">Sair</a>
    </header>

    <main>
        <h2>Painel de Controle</h2>

        <?php if ($nivel_acesso == 'administrador'): ?>
            <h3>Administração</h3>
            <ul>
                <li><a href="gerenciar_usuarios.php">Gerenciar Usuários</a></li>
                <li><a href="gerar_relatorios.php">Gerar Relatórios</a></li>
                <li><a href="gerenciar_pedidos.php">Gerenciar Pedidos Online</a></li>
                <li><a href="gerenciar_entregas.php">Gerenciar entregas</a></li>

            </ul>
        <?php endif; ?>

        <?php if ($nivel_acesso == 'administrador' || $nivel_acesso == 'gerente'): ?>
            <h3>Estoque e Produtos</h3>
            <ul>
                <li><a href="gerar_consultas.php">Gerar Consultas no Estoque</a></li>
                <li><a href="gerenciar_estoque.php">Gerenciar Estoques</a></li>
                <li><a href="cadastrar_produtos.php">Cadastrar Produtos</a></li>
            </ul>
        <?php endif; ?>

        <?php if ($nivel_acesso == 'administrador' || $nivel_acesso == 'gerente' || $nivel_acesso == 'usuario'): ?>
            <h3>Operações Gerais</h3>
            <ul>
                <li><a href="ver_estoque.php">Ver Estoque</a></li>
                <li><a href="realizar_pedido.php">Realizar Pedido</a></li>
                <li><a href="minhas_entregas.php">Ver status das entregas</a></li>
                <li><a href="ver_pedidos.php">Ver seus pedidos</a></li>
            </ul>
        <?php endif; ?>
    </main>
</body>
</html>
