<?php 
include 'config.php'; 

// --- FUNÇÃO DE SEGURANÇA (Para evitar ataques XSS) ---
function e($texto) {
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

// --- 1. LÓGICA DE PAGINAÇÃO ---
$limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;
$inicio = ($pagina - 1) * $limite;

// --- 2. FILTROS DE BUSCA ---
$busca_termo = $_GET['busca'] ?? '';
$data_pesquisa = $_GET['data_pesquisa'] ?? '';

$busca_param = "%" . $busca_termo . "%";
$params = ['busca1' => $busca_param, 'busca2' => $busca_param];
$filtro_sql = "";

if (!empty($data_pesquisa)) {
    $filtro_sql = " AND DATE(i.data_entrada) = :data_alvo";
    $params['data_alvo'] = $data_pesquisa;
}

// --- 3. LÓGICA DE ORDENAÇÃO ---
$colunas_permitidas = [
    'patrimonio' => 'i.patrimonio',
    'tipo'       => 'm.tipo',
    'entrada'    => 'i.data_entrada',
    'saida'      => 'i.data_saida',
    'local'      => 'l.nome',
    'usuario'    => 'u.nome'
];

$ordem = isset($_GET['ordem']) && array_key_exists($_GET['ordem'], $colunas_permitidas) ? $_GET['ordem'] : 'id_itens';
$direcao = isset($_GET['direcao']) && strtoupper($_GET['direcao']) === 'ASC' ? 'ASC' : 'DESC';
$proxima_direcao = ($direcao === 'ASC') ? 'DESC' : 'ASC';
$seta = ($direcao === 'ASC') ? ' ▲' : ' ▼';

// --- 4. CONTAGEM TOTAL (Apenas itens ativos) ---
$sql_total = "SELECT COUNT(*) as total FROM movimentacao_itens i
              JOIN movimentacao m ON i.movimentacao = m.id_movimentacao
              JOIN localidade l ON m.localidade = l.id_localidade
              WHERE i.ativo = 1 AND (i.patrimonio LIKE :busca1 OR l.nome LIKE :busca2) $filtro_sql";
$stmt_total = $pdo->prepare($sql_total);
$stmt_total->bindValue(':busca1', $params['busca1'], PDO::PARAM_STR);
$stmt_total->bindValue(':busca2', $params['busca2'], PDO::PARAM_STR);
if(isset($params['data_alvo'])) $stmt_total->bindValue(':data_alvo', $params['data_alvo'], PDO::PARAM_STR);
$stmt_total->execute();
$total_registros = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$total_paginas = ceil($total_registros / $limite);

// Função para links de cabeçalho
function linkOrdem($coluna, $label, $ordemAtual, $proxima, $busca, $limite, $seta, $data) {
    $icone = ($ordemAtual === $coluna) ? $seta : '';
    return "<th><a href='?ordem=$coluna&direcao=$proxima&busca=".urlencode($busca)."&limite=$limite&data_pesquisa=$data'>$label$icone</a></th>";
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
    <?php include 'header.php'; ?>
    <h2>Consultar Movimentações</h2>
    
    <div class="search-container">
        <form method="GET" class="search-row">
            <div class="search-group">
                <label>Patrimônio / Localidade:</label>
                <input type="text" name="busca" placeholder="Buscar..." value="<?php echo e($busca_termo); ?>">
            </div>
            <div class="search-group" style="flex: 0;">
                <label>Data:</label>
                <input type="date" name="data_pesquisa" value="<?php echo e($data_pesquisa); ?>">
            </div>
            <input type="hidden" name="limite" value="<?php echo $limite; ?>">
            <button type="submit">Pesquisar</button>
            <a href="index.php" class="btn-clear">Limpar</a>
        </form>
    </div>

    <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
        <span>Total: <strong><?php echo $total_registros; ?></strong> registros</span>
        
        <form method="GET">
            <input type="hidden" name="busca" value="<?php echo e($busca_termo); ?>">
            <input type="hidden" name="data_pesquisa" value="<?php echo e($data_pesquisa); ?>">
            <label style="font-size: 13px; font-weight: bold;">Mostrar: </label>
            <select name="limite" onchange="this.form.submit()">
                <option value="10" <?php if($limite==10) echo 'selected'; ?>>10</option>
                <option value="50" <?php if($limite==50) echo 'selected'; ?>>50</option>
                <option value="100" <?php if($limite==100) echo 'selected'; ?>>100</option>
            </select>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <?php echo linkOrdem('patrimonio', 'Patrimônio', $ordem, $proxima_direcao, $busca_termo, $limite, $seta, $data_pesquisa); ?>
                <?php echo linkOrdem('tipo', 'Tipo', $ordem, $proxima_direcao, $busca_termo, $limite, $seta, $data_pesquisa); ?>
                <?php echo linkOrdem('entrada', 'Entrada', $ordem, $proxima_direcao, $busca_termo, $limite, $seta, $data_pesquisa); ?>
                <?php echo linkOrdem('saida', 'Saída', $ordem, $proxima_direcao, $busca_termo, $limite, $seta, $data_pesquisa); ?>
                <?php echo linkOrdem('local', 'Localidade', $ordem, $proxima_direcao, $busca_termo, $limite, $seta, $data_pesquisa); ?>
                <?php echo linkOrdem('usuario', 'Usuário', $ordem, $proxima_direcao, $busca_termo, $limite, $seta, $data_pesquisa); ?>
                <th>Assinatura</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $coluna_sql = $colunas_permitidas[$ordem] ?? 'i.id_itens';
            $sql = "SELECT i.id_itens, i.patrimonio, i.data_entrada, i.data_saida, m.id_movimentacao, 
                           m.tipo, m.assinatura, l.nome as local, u.nome as user
                    FROM movimentacao_itens i
                    JOIN movimentacao m ON i.movimentacao = m.id_movimentacao
                    JOIN localidade l ON m.localidade = l.id_localidade
                    JOIN usuario u ON m.usuario = u.id_usuario
                    WHERE i.ativo = 1 AND (i.patrimonio LIKE :busca1 OR l.nome LIKE :busca2) $filtro_sql
                    ORDER BY $coluna_sql $direcao
                    LIMIT :inicio, :limite"; 
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':busca1', $params['busca1'], PDO::PARAM_STR);
            $stmt->bindValue(':busca2', $params['busca2'], PDO::PARAM_STR);
            if(isset($params['data_alvo'])) $stmt->bindValue(':data_alvo', $params['data_alvo'], PDO::PARAM_STR);
            $stmt->bindValue(':inicio', (int)$inicio, PDO::PARAM_INT);
            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $stmt->execute();

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $entrada = ($row['data_entrada']) ? date('d/m/Y', strtotime($row['data_entrada'])) : "---";
                $saida = ($row['data_saida'] && $row['data_saida'] != '0000-00-00') 
                         ? date('d/m/Y', strtotime($row['data_saida'])) 
                         : "<a href='saida.php?id_item=".urlencode($row['patrimonio'])."' style='color:#e67e22; text-decoration:none; font-weight:bold;'>Registrar</a>";

                $assinatura = (!empty($row['assinatura']) && $row['assinatura'] !== "0") 
                              ? "<img src='{$row['assinatura']}' class='img-assinatura' onclick='ampliarAssinatura(this.src)'>" 
                              : "---";

                echo "<tr>
                        <td><strong>".e($row['patrimonio'])."</strong></td>
                        <td><span class='badge-tipo'>".e($row['tipo'])."</span></td>
                        <td>{$entrada}</td>
                        <td>{$saida}</td>
                        <td>".e($row['local'])."</td>
                        <td>".e($row['user'])."</td>
                        <td>{$assinatura}</td>
                        <td>
                            <a href='editar.php?id={$row['id_movimentacao']}' style='color:#2980b9; text-decoration:none;'>Editar</a> | 
                            <a href='deletar.php?id_item={$row['id_itens']}' style='color:#e74c3c; text-decoration:none;' onclick='return confirm(\"Excluir item?\")'>Excluir</a>
                        </td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php for($i = 1; $i <= $total_paginas; $i++): ?>
            <a href="?pagina=<?php echo $i; ?>&busca=<?php echo urlencode($busca_termo); ?>&limite=<?php echo $limite; ?>&ordem=<?php echo $ordem; ?>&direcao=<?php echo $direcao; ?>&data_pesquisa=<?php echo $data_pesquisa; ?>" 
               class="<?php echo ($i == $pagina) ? 'active' : ''; ?>"><?php echo $i; ?></a>
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