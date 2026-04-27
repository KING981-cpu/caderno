<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recebe os IDs (O id_movimentacao vem do hidden, o id_item também)
    $id_movimentacao = $_POST['id_movimentacao'];
    $id_itens        = $_POST['id_itens'];
    
    // Recebe os novos dados
    $patrimonio   = $_POST['patrimonio'];
    $localidade   = $_POST['localidade'];
    $usuario      = $_POST['usuario'];
    $tipo         = $_POST['tipo'];
    $data_entrada = $_POST['data_entrada'];
    $observacao   = $_POST['observacao'];

    try {
        $pdo->beginTransaction();

        // 1. Atualiza a tabela principal (CABEÇALHO)
        $sql1 = "UPDATE movimentacao SET 
                    localidade = :local, 
                    usuario = :user, 
                    tipo = :tipo, 
                    observacao = :obs 
                 WHERE id_movimentacao = :id_m";
        
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([
            'local' => $localidade,
            'user'  => $usuario,
            'tipo'  => $tipo,
            'obs'   => $observacao,
            'id_m'  => $id_movimentacao
        ]);

        // 2. Atualiza a tabela de ITENS (PATRIMÔNIO E DATA)
        $sql2 = "UPDATE movimentacao_itens SET 
                    patrimonio = :pat, 
                    data_entrada = :data 
                 WHERE id_itens = :id_i";
        
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([
            'pat'  => $patrimonio,
            'data' => $data_entrada,
            'id_i' => $id_itens
        ]);

        $pdo->commit();
        echo "<script>alert('Atualizado com sucesso!'); window.location.href='index.php';</script>";

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Erro ao atualizar: " . $e->getMessage();
    }
} else {
    header("Location: index.php");
}
?>