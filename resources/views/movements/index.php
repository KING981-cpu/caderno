<?php
// Include header
$headerPath = dirname(__DIR__) . '/includes/header.php';
if (file_exists($headerPath)) {
    include $headerPath;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Caderno Digital - Pesquisa</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1400px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .search-container { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e9ecef; }
        .search-row { display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end; }
        .search-group { display: flex; flex-direction: column; flex: 1; min-width: 200px; }
        .search-group label { font-size: 12px; font-weight: bold; margin-bottom: 5px; color: #666; }
        input[type="text"], input[type="date"], select { padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { padding: 10px 25px; background: #27ae60; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-clear { background: #95a5a6; text-decoration: none; color: white; padding: 10px 15px; border-radius: 5px; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border-bottom: 1px solid #eee; padding: 12px; text-align: left; font-size: 14px; }
        th { background: #f8f9fa; }
        th a { text-decoration: none; color: #333; display: flex; align-items: center; }
        .badge-tipo { padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; background: #34495e; color: white; }
        .img-assinatura { width: 60px; height: auto; border: 1px solid #ddd; cursor: zoom-in; }
        .pagination { margin-top: 20px; display: flex; justify-content: center; gap: 5px; }
        .pagination a { padding: 8px 15px; border: 1px solid #ddd; color: #27ae60; text-decoration: none; border-radius: 4px; }
        .pagination a.active { background: #27ae60; color: white; border-color: #27ae60; }
    </style>
</head>
<body>

<div class="container">
    <h2>Consultar Movimentações</h2>
    
    <div class="search-container">
        <form method="GET" class="search-row">
            <div class="search-group">
                <label>Patrimônio / Localidade:</label>
                <input type="text" name="busca" placeholder="Buscar..." value="<?php echo e($search ?? ''); ?>">
            </div>
            <div class="search-group" style="flex: 0;">
                <label>Data:</label>
                <input type="date" name="data_pesquisa" value="<?php echo e($date ?? ''); ?>">
            </div>
            <input type="hidden" name="limite" value="<?php echo $limit ?? 10; ?>">
            <button type="submit">Pesquisar</button>
            <a href="index" class="btn-clear">Limpar</a>
        </form>
    </div>

    <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
        <span>Total: <strong><?php echo $total ?? 0; ?></strong> registros</span>
        
        <form method="GET">
            <input type="hidden" name="busca" value="<?php echo e($search ?? ''); ?>">
            <input type="hidden" name="data_pesquisa" value="<?php echo e($date ?? ''); ?>">
            <label style="font-size: 13px; font-weight: bold;">Mostrar: </label>
            <select name="limite" onchange="this.form.submit()">
                <option value="10" <?php if(($limit ?? 10)==10) echo 'selected'; ?>>10</option>
                <option value="50" <?php if(($limit ?? 10)==50) echo 'selected'; ?>>50</option>
                <option value="100" <?php if(($limit ?? 10)==100) echo 'selected'; ?>>100</option>
            </select>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th><a href="?ordem=patrimonio&direcao=<?php echo ($order ?? '') === 'patrimonio' ? 'ASC' : 'DESC'; ?>&busca=<?php echo urlencode($search ?? ''); ?>&limite=<?php echo $limit ?? 10; ?>&data_pesquisa=<?php echo $date ?? ''; ?>">Patrimônio <?php echo ($order ?? '') === 'patrimonio' ? ($direction === 'ASC' ? '▲' : '▼') : ''; ?></a></th>
                <th><a href="?ordem=tipo&direcao=<?php echo ($order ?? '') === 'tipo' ? 'ASC' : 'DESC'; ?>&busca=<?php echo urlencode($search ?? ''); ?>&limite=<?php echo $limit ?? 10; ?>&data_pesquisa=<?php echo $date ?? ''; ?>">Tipo <?php echo ($order ?? '') === 'tipo' ? ($direction === 'ASC' ? '▲' : '▼') : ''; ?></a></th>
                <th><a href="?ordem=entrada&direcao=<?php echo ($order ?? '') === 'entrada' ? 'ASC' : 'DESC'; ?>&busca=<?php echo urlencode($search ?? ''); ?>&limite=<?php echo $limit ?? 10; ?>&data_pesquisa=<?php echo $date ?? ''; ?>">Entrada <?php echo ($order ?? '') === 'entrada' ? ($direction === 'ASC' ? '▲' : '▼') : ''; ?></a></th>
                <th><a href="?ordem=saida&direcao=<?php echo ($order ?? '') === 'saida' ? 'ASC' : 'DESC'; ?>&busca=<?php echo urlencode($search ?? ''); ?>&limite=<?php echo $limit ?? 10; ?>&data_pesquisa=<?php echo $date ?? ''; ?>">Saída <?php echo ($order ?? '') === 'saida' ? ($direction === 'ASC' ? '▲' : '▼') : ''; ?></a></th>
                <th><a href="?ordem=local&direcao=<?php echo ($order ?? '') === 'local' ? 'ASC' : 'DESC'; ?>&busca=<?php echo urlencode($search ?? ''); ?>&limite=<?php echo $limit ?? 10; ?>&data_pesquisa=<?php echo $date ?? ''; ?>">Localidade <?php echo ($order ?? '') === 'local' ? ($direction === 'ASC' ? '▲' : '▼') : ''; ?></a></th>
                <th><a href="?ordem=usuario&direcao=<?php echo ($order ?? '') === 'usuario' ? 'ASC' : 'DESC'; ?>&busca=<?php echo urlencode($search ?? ''); ?>&limite=<?php echo $limit ?? 10; ?>&data_pesquisa=<?php echo $date ?? ''; ?>">Usuário <?php echo ($order ?? '') === 'usuario' ? ($direction === 'ASC' ? '▲' : '▼') : ''; ?></a></th>
                <th>Assinatura</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($movements ?? [] as $row): ?>
                <?php
                $entrada = ($row['data_entrada']) ? date('d/m/Y', strtotime($row['data_entrada'])) : "---";
                $saida = ($row['data_saida'] && $row['data_saida'] !== '0000-00-00')
                         ? date('d/m/Y', strtotime($row['data_saida']))
                         : "---";
                $assinatura = (!empty($row['assinatura']) && $row['assinatura'] !== "0")
                              ? "<img src='" . $row['assinatura'] . "' class='img-assinatura' onclick='ampliarAssinatura(this.src)'>"
                              : "---";
                ?>
                <tr>
                    <td><strong><?php echo e($row['patrimonio'] ?? ''); ?></strong></td>
                    <td><span class='badge-tipo'><?php echo e($row['tipo'] ?? ''); ?></span></td>
                    <td><?php echo $entrada; ?></td>
                    <td><?php echo $saida; ?></td>
                    <td><?php echo e($row['local_nome'] ?? ''); ?></td>
                    <td><?php echo e($row['usuario_nome'] ?? ''); ?></td>
                    <td><?php echo $assinatura; ?></td>
                    <td>
                        <a href='editar?id=<?php echo $row['id_movimentacao']; ?>' style='color:#2980b9; text-decoration:none;'>Editar</a> | 
                        <a href='deletar?id_item=<?php echo $row['id_itens']; ?>' style='color:#e74c3c; text-decoration:none;' onclick='return confirm("Excluir item?")'>Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php for($i = 1; $i <= ($pages ?? 1); $i++): ?>
            <a href="?pagina=<?php echo $i; ?>&busca=<?php echo urlencode($search ?? ''); ?>&limite=<?php echo $limit ?? 10; ?>&ordem=<?php echo $order ?? ''; ?>&direcao=<?php echo $direction ?? ''; ?>&data_pesquisa=<?php echo $date ?? ''; ?>" 
               class="<?php echo ($i == ($page ?? 1)) ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
</div>

<script>
function ampliarAssinatura(base64) {
    const win = window.open("");
    win.document.write("<html><body style='margin:0; display:flex; align-items:center; justify-content:center; background:#333; height:100vh;'><img src='" + base64 + "' style='max-width:90vw; background:white; padding:20px; border-radius:10px;'><br><button onclick='window.close()' style='position:absolute; bottom:20px; cursor:pointer;'>Fechar</button></body></html>");
}
</script>
</body>
</html>
