<?php
include dirname(__DIR__) . '/config.php';

$id_item = $_GET['id_item'] ?? null;

if (!$id_item) {
    echo "ID do item não fornecido.";
    exit;
}

// Buscar informações do item
$sql = "SELECT i.id_itens, i.patrimonio, i.data_entrada, m.id_movimentacao, l.nome as local, u.nome as user
        FROM movimentacao_itens i
        JOIN movimentacao m ON i.movimentacao = m.id_movimentacao
        JOIN localidade l ON m.localidade = l.id_localidade
        JOIN usuario u ON m.usuario = u.id_usuario
        WHERE i.id_itens = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_item]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    echo "Item não encontrado.";
    exit;
}

// Se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data_saida = $_POST['data_saida'] ?? date('Y-m-d');
    
    $sql_update = "UPDATE movimentacao_itens SET data_saida = ? WHERE id_itens = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$data_saida, $id_item]);
    
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Registrar Saída</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #27ae60; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        input[type="date"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .buttons { display: flex; gap: 10px; }
        button { padding: 10px 20px; background: #27ae60; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        button:hover { background: #229954; }
        .btn-cancel { background: #95a5a6; text-decoration: none; color: white; padding: 10px 20px; border-radius: 5px; }
        .btn-cancel:hover { background: #7f8c8d; }
    </style>
</head>
<body>

<div class="container">
    <h2>Registrar Saída de Patrimônio</h2>
    
    <div class="info">
        <p><strong>Patrimônio:</strong> <?php echo htmlspecialchars($item['patrimonio']); ?></p>
        <p><strong>Data Entrada:</strong> <?php echo date('d/m/Y', strtotime($item['data_entrada'])); ?></p>
        <p><strong>Localidade:</strong> <?php echo htmlspecialchars($item['local']); ?></p>
        <p><strong>Responsável:</strong> <?php echo htmlspecialchars($item['user']); ?></p>
    </div>
    
    <form method="POST">
        <div class="form-group">
            <label for="data_saida">Data de Saída:</label>
            <input type="date" id="data_saida" name="data_saida" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        
        <div class="buttons">
            <button type="submit">Registrar Saída</button>
            <a href="index.php" class="btn-cancel">Cancelar</a>
        </div>
    </form>
</div>

</body>
</html>
