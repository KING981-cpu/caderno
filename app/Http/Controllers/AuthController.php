<?php

namespace App\Http\Controllers;

use App\Core\BaseController;
use App\Services\AuthService;
use App\SRE\Logger;

class AuthController extends BaseController
{
    private AuthService $authService;

    public function __construct()
    {
        parent::__construct();
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => $this->isSecure(),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
        $this->authService = new AuthService();
    }

    public function login(): void
    {
        if ($this->request->isPost()) {
            $cpf = trim($this->request->get('cpf', ''));
            $password = $this->request->get('senha', '');

            $user = $this->authService->authenticate($cpf, $password);

            if ($user) {
                $this->authService->startSession($user);
                Logger::info('User login successful', ['user_id' => $user['id_usuario']]);
                $this->redirect('/');
                return;
            }

            Logger::warning('Login attempt failed for CPF: ' . $cpf);
            $this->view('auth/login', [
                'error' => 'CPF ou Senha incorretos!'
            ]);
            return;
        }

        // GET request - show login form
        $this->view('auth/login');
    }

    public function logout(): void
    {
        $this->authService->endSession();
        Logger::info('User logged out');
        $this->redirect('/login');
    }

    public function requireAuth(): void
    {
        if (!$this->authService->isAuthenticated()) {
            $this->redirect('/caderno/login');
        }
    }

    private function isSecure(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    }
}
