<?php include dirname(__DIR__) . '/includes/header.php'; ?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lixeira</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1400px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #eee; padding: 12px; text-align: left; }
        th { background: #f8f9fa; }
        .img-assinatura { width: 60px; height: auto; border: 1px solid #ddd; cursor: zoom-in; }
        .empty { padding: 40px; text-align: center; color: #666; }
        .badge-type { background: #7f8c8d; color: white; padding: 5px 10px; border-radius: 4px; font-size: 12px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Lixeira</h2>
    <p>Registros excluídos (ativo = 0).</p>

    <?php if (!empty($items)): ?>
        <table>
            <thead>
                <tr>
                    <th>Patrimônio</th>
                    <th>Tipo</th>
                    <th>Entrada</th>
                    <th>Saída</th>
                    <th>Localidade</th>
                    <th>Usuário</th>
                    <th>Assinatura</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $row): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($row['patrimonio'] ?? ''); ?></strong></td>
                        <td><span class="badge-type"><?php echo htmlspecialchars($row['tipo'] ?? ''); ?></span></td>
                        <td><?php echo !empty($row['data_entrada']) ? date('d/m/Y', strtotime($row['data_entrada'])) : '---'; ?></td>
                        <td><?php echo (!empty($row['data_saida']) && $row['data_saida'] !== '0000-00-00') ? date('d/m/Y', strtotime($row['data_saida'])) : '---'; ?></td>
                        <td><?php echo htmlspecialchars($row['local'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['user'] ?? ''); ?></td>
                        <td>
                            <?php if (!empty($row['assinatura']) && $row['assinatura'] !== '0'): ?>
                                <img src="<?php echo $row['assinatura']; ?>" class="img-assinatura" onclick="ampliarAssinatura(this.src)">
                            <?php else: ?>
                                ---
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty">Nenhum registro excluído encontrado.</div>
    <?php endif; ?>
</div>

<script>
function ampliarAssinatura(base64) {
    const win = window.open("");
    win.document.write("<html><body style='margin:0; display:flex; align-items:center; justify-content:center; background:#333; height:100vh;'><img src='" + base64 + "' style='max-width:90vw; background:white; padding:20px; border-radius:10px;'><br><button onclick='window.close()' style='position:absolute; bottom:20px; cursor:pointer;'>Fechar</button></body></html>");
}
</script>
</body>
</html>
