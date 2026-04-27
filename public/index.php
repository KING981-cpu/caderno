<?php
// Public entry point for the application
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/bootstrap.php';

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\HealthController;
use App\SRE\Logger;

// Get the requested path
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/';
$route = str_replace($basePath, '', $requestPath);

// Remove trailing slash
$route = rtrim($route, '/');

// Route the request
try {
    // Health check endpoint
    if ($route === 'health') {
        $controller = new HealthController();
        $controller->check();
    }

    // API routes for equipment
    elseif (strpos($route, 'api/equipamentos') === 0) {
        $controller = new EquipmentController();
        if ($controller->request->isGet()) {
            $controller->list();
        } elseif ($controller->request->isPost()) {
            $controller->store();
        }
    }

    elseif (strpos($route, 'api/equipamento') === 0) {
        $controller = new EquipmentController();
        if ($controller->request->isGet()) {
            $controller->get();
        } elseif ($controller->request->isPut() || $controller->request->isPatch()) {
            $controller->update();
        } elseif ($controller->request->isDelete()) {
            $controller->delete();
        }
    }

    // Authentication routes
    elseif ($route === 'autenticar' || $route === 'autenticar.php') {
        $controller = new AuthController();
        $controller->login();
    }

    elseif ($route === 'sair' || $route === 'sair.php') {
        $controller = new AuthController();
        $controller->logout();
    }

    // Movement routes
    elseif ($route === 'index' || $route === 'index.php' || $route === '') {
        $controller = new MovementController();
        // Require authentication
        $auth = new AuthController();
        $auth->requireAuth();
        $controller->index();
    }

    elseif ($route === 'cadastro' || $route === 'cadastro.php') {
        $controller = new MovementController();
        $auth = new AuthController();
        $auth->requireAuth();
        $controller->create();
    }

    elseif ($route === 'salvar' || $route === 'salvar.php') {
        $controller = new MovementController();
        $auth = new AuthController();
        $auth->requireAuth();
        $controller->store();
    }

    elseif ($route === 'editar' || $route === 'editar.php') {
        $controller = new MovementController();
        $auth = new AuthController();
        $auth->requireAuth();
        $controller->edit();
    }

    elseif ($route === 'atualizar' || $route === 'atualizar.php') {
        $controller = new MovementController();
        $auth = new AuthController();
        $auth->requireAuth();
        $controller->update();
    }

    elseif ($route === 'pendentes' || $route === 'pendentes.php') {
        $controller = new MovementController();
        $auth = new AuthController();
        $auth->requireAuth();
        $controller->pending();
    }

    elseif ($route === 'saida' || $route === 'saida.php') {
        $controller = new MovementController();
        $auth = new AuthController();
        $auth->requireAuth();
        $controller->recordSaida();
    }

    elseif ($route === 'deletar' || $route === 'deletar.php') {
        $controller = new MovementController();
        $auth = new AuthController();
        $auth->requireAuth();
        $controller->delete();
    }

    elseif ($route === 'cadastrar_rapido' || $route === 'cadastrar_rapido.php') {
        $controller = new ReferenceController();
        $auth = new AuthController();
        $auth->requireAuth();
        $controller->createQuick();
    }

    elseif ($route === 'login' || $route === 'login.php' || $route === 'login') {
        // Serve the login page
        require_once dirname(__DIR__) . '/resources/views/auth/login.php';
    }

    else {
        // 404 Not Found
        http_response_code(404);
        echo json_encode(['error' => 'Route not found: ' . $requestPath]);
    }
} catch (\Exception $e) {
    Logger::error('Route handling error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
