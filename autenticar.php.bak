<?php
include 'config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cpf = trim($_POST['cpf']);
    $senha = trim($_POST['senha']);

    // Busca o usuário no banco
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE cpf = :cpf");
    $stmt->execute(['cpf' => $cpf]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Comparação direta de texto
    if ($user && $senha == $user['senha']) {
        // Define as sessões
        $_SESSION['usuario_id'] = $user['id_usuario'];
        $_SESSION['usuario_nome'] = $user['nome'];

        // Redireciona
        header("Location: index.php");
        exit();
    } else {
        // Se falhar, avisa e volta
        echo "<script>alert('CPF ou Senha incorretos!'); window.location.href='login.php';</script>";
    }
}
?>