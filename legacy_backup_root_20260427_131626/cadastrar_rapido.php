<?php
// cadastrar_rapido.php
include 'config.php';


$tabela = $_POST['tabela'];
$nome = $_POST['nome'];

try {
    if ($tabela == 'localidade') {
        $sql = "INSERT INTO localidade (nome) VALUES (:nome)";
    } else {
        $sql = "INSERT INTO usuario (nome) VALUES (:nome)";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['nome' => $nome]);
    $id = $pdo->lastInsertId();
    
    echo json_encode(['id' => $id]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>