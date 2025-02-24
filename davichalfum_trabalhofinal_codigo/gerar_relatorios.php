<?php
session_start();
require_once 'conexao.php';
require_once 'vendor/autoload.php';

$data_inicial = isset($_POST['data_inicial']) ? $_POST['data_inicial'] : '';
$data_final = isset($_POST['data_final']) ? $_POST['data_final'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : '';
$usuario = isset($_POST['usuario']) ? $_POST['usuario'] : '';
$pedidos = [];

try {
    $query = "SELECT p.id, p.data_pedido, p.status, u.nome AS usuario_nome 
              FROM pedidos p
              JOIN usuarios u ON p.id_usuario = u.id
              WHERE 1=1";
    
    if ($data_inicial) {
        $query .= " AND p.data_pedido >= :data_inicial";
    }

    if ($data_final) {
        $query .= " AND p.data_pedido <= :data_final";
    }

    if ($status) {
        $query .= " AND p.status = :status";
    }

    if ($usuario) {
        $query .= " AND u.nome LIKE :usuario";
    }

    $stmt = $conn->prepare($query);

    if ($data_inicial) {
        $stmt->bindParam(':data_inicial', $data_inicial);
    }
    if ($data_final) {
        $stmt->bindParam(':data_final', $data_final);
    }
    if ($status) {
        $stmt->bindParam(':status', $status);
    }
    if ($usuario) {
        $usuario = "%" . $usuario . "%";
        $stmt->bindParam(':usuario', $usuario);
    }

    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $erro = "Erro no sistema: " . $e->getMessage();
}

function gerarPDF($pedidos) {
    $pdf = new TCPDF();
    $pdf->SetCreator('Supermercado');
    $pdf->SetAuthor('Admin');
    $pdf->SetTitle('Relatório de Pedidos');
    $pdf->SetSubject('Pedidos Filtrados');
    $pdf->SetMargins(15, 30, 15);
    $pdf->AddPage();
    
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Relatório de Pedidos', 0, 1, 'C');
    $pdf->Ln(5);
    
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(30, 10, 'ID', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Data do Pedido', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Status', 1, 0, 'C');
    $pdf->Cell(70, 10, 'Usuario', 1, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 12);
    foreach ($pedidos as $pedido) {
        $pdf->Cell(30, 10, $pedido['id'], 1, 0, 'C');
        $pdf->Cell(40, 10, $pedido['data_pedido'], 1, 0, 'C');
        $pdf->Cell(40, 10, $pedido['status'], 1, 0, 'C');
        $pdf->Cell(70, 10, $pedido['usuario_nome'], 1, 1, 'L');
    }
    
    $pdf->Output('relatorio_pedidos.pdf', 'I');
    exit();
}

if (isset($_POST['gerar_pdf'])) {
    gerarPDF($pedidos);
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerar Relatório de Pedidos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Gerar Relatório de Pedidos</h1>
    </header>
    
    <main>
        <h2>Filtrar Pedidos para Relatório</h2>
        
        <form method="POST">
            <label for="data_inicial">Data Inicial:</label>
            <input type="date" name="data_inicial" id="data_inicial" value="<?= htmlspecialchars($data_inicial) ?>">
            <br>

            <label for="data_final">Data Final:</label>
            <input type="date" name="data_final" id="data_final" value="<?= htmlspecialchars($data_final) ?>">
            <br>

            <label for="status">Status:</label>
            <select name="status" id="status">
                <option value="">Selecione</option>
                <option value="pendente" <?= ($status == 'pendente') ? 'selected' : ''; ?>>Pendente</option>
                <option value="concluido" <?= ($status == 'concluido') ? 'selected' : ''; ?>>Concluído</option>
                <option value="cancelado" <?= ($status == 'cancelado') ? 'selected' : ''; ?>>Cancelado</option>
            </select>
            <br>

            <label for="usuario">Usuário:</label>
            <input type="text" name="usuario" id="usuario" value="<?= htmlspecialchars($usuario) ?>">
            <br>

            <button type="submit">Gerar Relatório</button>
            <button type="submit" name="gerar_pdf">Gerar PDF</button>
        </form>

        
        <a href="dashboard.php" class="btn-voltar">Voltar para o Dashboard</a>

        <?php if (isset($erro)): ?>
            <p style="color: red;"><?= $erro ?></p>
        <?php endif; ?>

        <?php if (count($pedidos) > 0): ?>
            <h3>Relatório de Pedidos Encontrados:</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data do Pedido</th>
                        <th>Status</th>
                        <th>Usuário</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?= $pedido['id'] ?></td>
                            <td><?= $pedido['data_pedido'] ?></td>
                            <td><?= htmlspecialchars($pedido['status']) ?></td>
                            <td><?= htmlspecialchars($pedido['usuario_nome']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum pedido encontrado com os filtros selecionados.</p>
        <?php endif; ?>
    </main>
</body>
</html>
