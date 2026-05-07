<?php
include 'config.php';

session_start();

// 1. Verificar se o patrimônio veio pela URL
if (!isset($_GET['id_item'])) {
    die("Patrimônio não especificado.");
}

$patrimonio = $_GET['id_item'];

// 2. Se o formulário for enviado (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $data_saida = $_POST['data_saida'] ?? '';
        $dataObj = DateTime::createFromFormat('Y-m-d', $data_saida);
        if (!$dataObj || $dataObj->format('Y-m-d') !== $data_saida) {
            throw new Exception('Data de saída inválida.');
        }

        $stmtCheck = $pdo->prepare("SELECT id_itens, data_entrada FROM movimentacao_itens WHERE patrimonio = :pat AND ativo = 1 AND (data_saida IS NULL OR data_saida = '0000-00-00') ORDER BY id_itens DESC LIMIT 1");
        $stmtCheck->execute(['pat' => $patrimonio]);
        $item = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            throw new Exception('Não foi encontrado um registro de entrada aberto para este patrimônio.');
        }
        if (!empty($item['data_entrada']) && $item['data_entrada'] > $data_saida) {
            throw new Exception('A saída não pode ser anterior à data de entrada.');
        }

        $sql = "UPDATE movimentacao_itens 
                SET data_saida = :data 
                WHERE id_itens = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'data' => $data_saida,
            'id'   => $item['id_itens']
        ]);

        echo "<script>alert('Saída registrada com sucesso!'); window.location.href='index.php';</script>";
    } catch (Exception $e) {
        echo "Erro ao registrar saída: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Registrar Saída</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 50px; }
        .box { background: white; padding: 20px; border-radius: 8px; max-width: 400px; margin: auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #e67e22; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

<div class="box">
    <h2>Registrar Saída</h2>
    <p>Patrimônio: <strong><?php echo htmlspecialchars($patrimonio); ?></strong></p>
    
    <form method="POST">
        <label>Data da Saída:</label>
        <input type="date" name="data_saida" value="<?php echo date('Y-m-d'); ?>" required>
        <button type="submit">Confirmar Saída</button>
        <br><br>
        <a href="index.php">Cancelar</a>
    </form>
</div>

</body>
</html>