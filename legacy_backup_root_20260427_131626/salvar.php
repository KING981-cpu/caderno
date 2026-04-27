<?php
include 'config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        // 1. Coleta dos dados do formulário
        $tipo       = $_POST['tipo'] ?? 'Entrada';
        $data_valor = $_POST['entrada'] ?? date('Y-m-d');
        $obs        = $_POST['observacao'] ?? '';
        $local      = $_POST['localidade'] ?? null;
        $user       = $_POST['usuario'] ?? null;
        $pat_brutos = $_POST['patrimonio'] ?? '';
        $assinatura = $_POST['assinatura_data'] ?? null; // Recebe a imagem Base64

        // 2. Inserir na tabela 'movimentacao' (Cabeçalho com Assinatura)
        $sqlMov = "INSERT INTO movimentacao (observacao, localidade, usuario, tipo, assinatura) 
                   VALUES (:obs, :local, :user, :tipo, :assinatura)";
        
        $stmtMov = $pdo->prepare($sqlMov);
        $stmtMov->execute([
            'obs'        => $obs,
            'local'      => $local,
            'user'       => $user,
            'tipo'       => $tipo,
            'assinatura' => $assinatura
        ]);

        // Pegamos o ID gerado para usar nos itens
        $idMov = $pdo->lastInsertId();

        // 3. Preparar a inserção dos itens (Patrimônios)
        $colunaData = ($tipo == 'Entrada') ? 'data_entrada' : 'data_saida';
        
        $sqlItem = "INSERT INTO movimentacao_itens (patrimonio, movimentacao, $colunaData) 
                    VALUES (:pat, :mov, :data_item)";
        $stmtItem = $pdo->prepare($sqlItem);

        // Quebra a string de patrimônios por vírgula e remove espaços
        $lista = explode(',', $pat_brutos);
        foreach ($lista as $p) {
            $p = trim($p);
            if (!empty($p)) {
                $stmtItem->execute([
                    'pat'       => $p,
                    'mov'       => $idMov,
                    'data_item' => $data_valor
                ]);
            }
        }

        // Finaliza a transação com sucesso
        $pdo->commit();
        echo "<script>alert('Salvo com sucesso!'); window.location.href='index.php';</script>";

    } catch (Exception $e) {
        // Se der qualquer erro, desfaz o que foi feito no banco
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo "Erro ao salvar: " . $e->getMessage();
    }
}