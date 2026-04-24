<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login Simples</title>
    <style>
        body { font-family: sans-serif; background: #1e3c72; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .card { background: white; padding: 30px; border-radius: 10px; width: 300px; text-align: center; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #2a5298; color: white; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Acesso</h2>
        <form action="autenticar.php" method="POST">
            <input type="text" name="cpf" placeholder="CPF (Ex: 123)" required>
            <input type="password" name="senha" placeholder="Senha (Ex: 123)" required>
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>