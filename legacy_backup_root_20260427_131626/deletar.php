<?php
include 'config.php';

// Verifique se o ID foi passado via URL
if (isset($_GET['id_item'])) {
    $id_para_desativar = $_GET['id_item'];
    
    try {
        // IMPORTANTE: Em vez de DELETE, usamos UPDATE para mudar o status
        // Estamos assumindo que você adicionou a coluna 'ativo' na tabela
        $sql = "UPDATE movimentacao_itens SET ativo = 0 WHERE id_itens = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id_para_desativar]);

        echo "<script>alert('Registro desativado com sucesso!'); window.location.href='index.php';</script>";
    } catch (Exception $e) {
        echo "Erro ao processar: " . $e->getMessage();
    }
} else {
    // Se não houver ID, volta para o início
    header("Location: index.php");
    exit;
}
?>