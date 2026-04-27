<?php include dirname(__DIR__) . '/includes/header.php'; ?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Itens Pendentes (Sem Saída)</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #eee; padding: 12px; text-align: left; }
        th { background: #2c3e50; color: white; }
        .btn-saida { background: #e67e22; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; }
        .alerta { color: #e74c3c; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h2>📦 Itens Atualmente no Setor</h2>
    <p>Estes itens deram entrada, mas a saída ainda não foi registrada.</p>

    <table>
        <thead>
            <tr>
                <th>Patrimônio</th>
                <th>Data de Entrada</th>
                <th>Localidade</th>
                <th>Responsável</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items ?? [] as $row): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['patrimonio']); ?></strong></td>
                    <td class='alerta'><?php echo date('d/m/Y', strtotime($row['data_entrada'])); ?></td>
                    <td><?php echo htmlspecialchars($row['local']); ?></td>
                    <td><?php echo htmlspecialchars($row['user']); ?></td>
                    <td>
                        <a href='saida?id_item=<?php echo urlencode($row['patrimonio']); ?>' class='btn-saida'>Registrar Saída</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if (empty($items)): ?>
        <p style="text-align:center; padding: 20px;">✅ Nenhum item pendente no momento!</p>
    <?php endif; ?>
</div>

</body>
</html>
