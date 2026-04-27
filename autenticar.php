<?php
include 'config.php';

// Configura parâmetros seguros para cookies de sessão
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'] ?? '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cpf = trim($_POST['cpf'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // Busca o usuário no banco
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE cpf = :cpf");
    $stmt->execute(['cpf' => $cpf]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $stored = $user['senha'] ?? '';

        // Verifica com password_verify (suporta hashes modernos)
        if (!empty($stored) && password_verify($senha, $stored)) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $user['id_usuario'];
            $_SESSION['usuario_nome'] = $user['nome'];

            header("Location: index.php");
            exit();
        }

        // Suporte a senhas legadas armazenadas em texto plano: migrar para hash
        if (!empty($stored) && $senha === $stored) {
            $newHash = password_hash($senha, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE usuario SET senha = :hash WHERE id_usuario = :id");
            $update->execute(['hash' => $newHash, 'id' => $user['id_usuario']]);

            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $user['id_usuario'];
            $_SESSION['usuario_nome'] = $user['nome'];

            header("Location: index.php");
            exit();
        }
    }

    // Mensagem genérica para falha de autenticação
    echo "<script>alert('CPF ou Senha incorretos!'); window.location.href='login.php';</script>";
}
?>
