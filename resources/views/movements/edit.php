<?php include dirname(__DIR__) . '/includes/header.php'; ?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Registro</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; display: flex; justify-content: center; padding: 40px; }
        .form-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 450px; }
        label { display: block; margin-top: 10px; font-weight: bold; font-size: 14px; color: #333; }
        input, textarea, select { width: 100%; padding: 10px; margin: 5px 0 15px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #2980b9; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background: #2471a3; }
        .btn-cancel { display:block; text-align:center; margin-top:15px; color: #666; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
    <div class="form-card">
        <h3>Editar Item #<?php echo htmlspecialchars($item['id_itens'] ?? ''); ?></h3>
        
        <form action="atualizar" method="POST">
            <input type="hidden" name="id_movimentacao" value="<?php echo htmlspecialchars($item['id_movimentacao'] ?? ''); ?>">
            <input type="hidden" name="id_itens" value="<?php echo htmlspecialchars($item['id_itens'] ?? ''); ?>">
            
            <label>Tipo de Movimentação:</label>
            <select name="tipo">
                <option value="Entrada" <?php echo (($item['tipo'] ?? '') == 'Entrada') ? 'selected' : ''; ?>>Entrada</option>
                <option value="Saída" <?php echo (($item['tipo'] ?? '') == 'Saída') ? 'selected' : ''; ?>>Saída</option>
            </select>

            <label>Patrimônio:</label>
            <input type="text" name="patrimonio" value="<?php echo htmlspecialchars($item['patrimonio'] ?? ''); ?>" required>

            <label>Localidade (Destino):</label>
            <select name="localidade" required>
                <?php foreach($localidades ?? [] as $l): ?>
                    <option value="<?php echo $l['id_localidade']; ?>" <?php echo (($l['id_localidade'] ?? '') == ($item['id_localidade'] ?? '')) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($l['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Usuário Responsável:</label>
            <select name="usuario" required>
                <?php foreach($usuarios ?? [] as $u): ?>
                    <option value="<?php echo $u['id_usuario']; ?>" <?php echo (($u['id_usuario'] ?? '') == ($item['id_usuario'] ?? '')) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($u['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Data Entrada:</label>
            <input type="date" name="data_entrada" value="<?php echo htmlspecialchars($item['data_entrada'] ?? ''); ?>" required>

            <button type="submit">Salvar Alterações</button>
            <a href="index" class="btn-cancel">Cancelar</a>
        </form>
    </div>
</body>
</html>
