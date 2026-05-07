<?php
include 'config.php';
include 'header.php';

$busca_termo = $_GET['busca'] ?? '';
$params = ['busca1' => "%{$busca_termo}%", 'busca2' => "%{$busca_termo}%"];

$sql = "SELECT i.id_itens, i.patrimonio, i.data_entrada, i.data_saida, m.tipo, m.assinatura, l.nome as local, u.nome as user
        FROM movimentacao_itens i
        JOIN movimentacao m ON i.movimentacao = m.id_movimentacao
        JOIN localidade l ON m.localidade = l.id_localidade
        JOIN usuario u ON m.usuario = u.id_usuario
        WHERE i.ativo = 0 AND (i.patrimonio LIKE :busca1 OR l.nome LIKE :busca2)
        ORDER BY i.data_entrada DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lixeira</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .search-container { margin-bottom: 20px; }
        input[type="text"] { padding: 10px; width: 260px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 10px 18px; background: #27ae60; color: white; border: none; border-radius: 5px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #eee; padding: 12px; text-align: left; }
        th { background: #f8f9fa; }
        .img-assinatura { width: 60px; height: auto; border: 1px solid #ddd; cursor: zoom-in; }
        .empty { padding: 40px; text-align: center; color: #666; }
    </style>
</head>
<body>
<div class="container">
    <h2>Lixeira</h2>
    <p>Visualize os registros excluídos (ativo = 0).</p>

    <div class="search-container">
        <form method="GET">
            <input type="text" name="busca" placeholder="Buscar patrimônio ou localidade" value="<?php echo htmlspecialchars($busca_termo); ?>">
            <button type="submit">Buscar</button>
        </form>
    </div>

    <?php if (!empty($rows)): ?>
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
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($row['patrimonio']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['tipo']); ?></td>
                        <td><?php echo $row['data_entrada'] ? date('d/m/Y', strtotime($row['data_entrada'])) : '---'; ?></td>
                        <td><?php echo ($row['data_saida'] && $row['data_saida'] !== '0000-00-00') ? date('d/m/Y', strtotime($row['data_saida'])) : '---'; ?></td>
                        <td><?php echo htmlspecialchars($row['local']); ?></td>
                        <td><?php echo htmlspecialchars($row['user']); ?></td>
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
    const win = window.open('');
    win.document.write("<html><body style='margin:0; display:flex; align-items:center; justify-content:center; background:#333; height:100vh;'><img src='" + base64 + "' style='max-width:90vw; background:white; padding:20px; border-radius:10px;'><button onclick='window.close()' style='position:absolute; bottom:20px; cursor:pointer;'>Fechar</button></body></html>");
}
</script>
</body>
</html>
