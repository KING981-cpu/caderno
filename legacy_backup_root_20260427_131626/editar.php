<?php 
include 'config.php'; 

// Mudamos para buscar pelo id_item, pois é ele que identifica a linha única na tabela
if (!isset($_GET['id'])) { header("Location: index.php"); exit; }

$id_item = $_GET['id'];

// Buscamos os dados unindo as tabelas
// Importante: pegamos o id_itens para poder atualizar o registro correto depois
$sql = "SELECT i.id_itens, i.patrimonio, i.data_entrada, i.data_saida, 
               m.id_movimentacao, m.tipo, m.localidade, m.usuario, m.observacao 
        FROM movimentacao_itens i
        JOIN movimentacao m ON i.movimentacao = m.id_movimentacao 
        WHERE i.id_itens = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id_item]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dados) { die("Registro não encontrado."); }
?>

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
        <h3>Editar Item #<?php echo $id_item; ?></h3>
        
        <form action="atualizar.php" method="POST">
            <input type="hidden" name="id_movimentacao" value="<?php echo $dados['id_movimentacao']; ?>">
            <input type="hidden" name="id_itens" value="<?php echo $dados['id_itens']; ?>">
            
            <label>Tipo de Movimentação:</label>
            <select name="tipo">
                <option value="Entrada" <?php echo ($dados['tipo'] == 'Entrada') ? 'selected' : ''; ?>>Entrada</option>
                <option value="Saída" <?php echo ($dados['tipo'] == 'Saída') ? 'selected' : ''; ?>>Saída</option>
            </select>

            <label>Patrimônio:</label>
            <input type="text" name="patrimonio" value="<?php echo $dados['patrimonio']; ?>" required>

            <label>Localidade (Destino):</label>
            <select name="localidade" required>
                <?php
                foreach($pdo->query("SELECT * FROM localidade ORDER BY nome ASC") as $l) {
                    $selecionado = ($l['id_localidade'] == $dados['localidade']) ? 'selected' : '';
                    echo "<option value='{$l['id_localidade']}' $selecionado>{$l['nome']}</option>";
                }
                ?>
            </select>

            <label>Usuário Responsável:</label>
            <select name="usuario" required>
                <?php
                foreach($pdo->query("SELECT * FROM usuario ORDER BY nome ASC") as $u) {
                    $selecionado = ($u['id_usuario'] == $dados['usuario']) ? 'selected' : '';
                    echo "<option value='{$u['id_usuario']}' $selecionado>{$u['nome']}</option>";
                }
                ?>
            </select>

            <label>Data Entrada:</label>
            <input type="date" name="data_entrada" value="<?php echo $dados['data_entrada']; ?>" required>

            <label>Data Saída:</label>
            <input type="date" name="data_saida" value="<?php echo $dados['data_saida']; ?>">

            <label>Observação:</label>
            <textarea name="observacao" rows="3"><?php echo $dados['observacao']; ?></textarea>

            <button type="submit">Salvar Alterações</button>
            <a href="index.php" class="btn-cancel">Cancelar</a>
        </form>
    </div>
</body>
</html>