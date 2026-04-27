<?php include dirname(__DIR__) . '/includes/header.php'; ?>

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
        <a href="index" style="text-decoration: none; text-align: center; display: block;">Cancelar</a>
    </form>
</div>

</body>
</html>
