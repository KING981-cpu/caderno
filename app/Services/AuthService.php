<?php

namespace App\Services;

use App\Models\Usuario;
use App\SRE\Logger;

class AuthService
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    public function authenticate(string $cpf, string $password): ?array
    {
        $user = $this->usuarioModel->findByCpf($cpf);

        if (!$user) {
            Logger::warning('Authentication attempt with non-existent CPF: ' . $cpf);
            return null;
        }

        $stored = $user['senha'] ?? '';

        // Try modern password hash
        if (!empty($stored) && password_verify($password, $stored)) {
            Logger::info('User authenticated successfully', ['user_id' => $user['id_usuario']]);
            return $user;
        }

        // Legacy plain text password support - migrate to hash
        if (!empty($stored) && $password === $stored) {
            $this->migratePassword($user['id_usuario'], $password);
            Logger::info('User authenticated with legacy password, now hashed', ['user_id' => $user['id_usuario']]);
            return $user;
        }

        Logger::warning('Authentication attempt with incorrect password for CPF: ' . $cpf);
        return null;
    }

    public function migratePassword(int $userId, string $plainPassword): void
    {
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        $this->usuarioModel->update($userId, ['senha' => $hash]);
    }

    public function startSession(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $user['id_usuario'];
        $_SESSION['usuario_nome'] = $user['nome'];
    }

    public function endSession(): void
    {
        session_destroy();
    }

    public function isAuthenticated(): bool
    {
        return isset($_SESSION['usuario_id']);
    }

    public function getCurrentUser(): ?array
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return [
            'id_usuario' => $_SESSION['usuario_id'],
            'nome' => $_SESSION['usuario_nome'],
        ];
    }
}
