<?php

namespace Tests\Unit;

use App\Services\AuthService;
use App\Models\Usuario;

class AuthServiceTest
{
    private AuthService $service;

    public function setUp(): void
    {
        $this->service = new AuthService();
    }

    public function testIsAuthenticatedReturnsFalseWhenNoSession(): void
    {
        session_destroy();
        $result = $this->service->isAuthenticated();
        assert($result === false, 'Expected isAuthenticated to return false when no session');
    }

    public function testIsAuthenticatedReturnsTrueWhenSessionExists(): void
    {
        $_SESSION['usuario_id'] = 1;
        $_SESSION['usuario_nome'] = 'Test User';
        $result = $this->service->isAuthenticated();
        assert($result === true, 'Expected isAuthenticated to return true when session exists');
    }

    public function testGetCurrentUserReturnsNullWhenNotAuthenticated(): void
    {
        session_destroy();
        $result = $this->service->getCurrentUser();
        assert($result === null, 'Expected getCurrentUser to return null when not authenticated');
    }
}
