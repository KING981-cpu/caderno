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

        $lista = array_filter(array_map('trim', explode(',', $pat_brutos)));
        if (empty($lista)) {
            throw new Exception('Informe ao menos um patrimônio.');
        }

        if (count($lista) !== count(array_unique($lista))) {
            throw new Exception('Há patrimônios duplicados na mesma entrada.');
        }

        $dataObj = DateTime::createFromFormat('Y-m-d', $data_valor);
        if (!$dataObj || $dataObj->format('Y-m-d') !== $data_valor) {
            throw new Exception('Data inválida.');
        }

        $stmtCheck = $pdo->prepare("SELECT data_entrada, data_saida FROM movimentacao_itens WHERE patrimonio = :pat AND ativo = 1 ORDER BY id_itens DESC LIMIT 1");
        foreach ($lista as $pat) {
            $pat = trim($pat);
            if ($pat === '') {
                continue;
            }

            $stmtCheck->execute(['pat' => $pat]);
            $last = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($tipo === 'Entrada') {
                if ($last && (empty($last['data_saida']) || $last['data_saida'] === '0000-00-00')) {
                    throw new Exception("O patrimônio '{$pat}' ainda não saiu. Registre a saída antes de uma nova entrada.");
                }
                if ($last && !empty($last['data_saida']) && $last['data_saida'] > $data_valor) {
                    throw new Exception("A entrada do patrimônio '{$pat}' não pode ser anterior à última saída ({$last['data_saida']}).");
                }
            } else {
                if (!$last || empty($last['data_entrada']) || $last['data_entrada'] === '0000-00-00') {
                    throw new Exception("Não há registro de entrada pendente para o patrimônio '{$pat}'.");
                }
                if (!empty($last['data_saida']) && $last['data_saida'] !== '0000-00-00') {
                    throw new Exception("O patrimônio '{$pat}' já está com saída registrada. Registre uma nova entrada antes de outra saída.");
                }
                if ($last['data_entrada'] > $data_valor) {
                    throw new Exception("A saída do patrimônio '{$pat}' não pode ser anterior à entrada ({$last['data_entrada']}).");
                }
            }
        }

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