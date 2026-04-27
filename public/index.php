<?php
// Public entry point for the application
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/bootstrap.php';

use App\Http\Controllers\MovementController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\HealthController;
use App\SRE\Logger;

// Get the requested path
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Detect base path dynamically - handle both /caderno/ and / (Docker deployment)
$basePath = '/';
if (strpos($requestPath, '/caderno/') === 0) {
    $basePath = '/caderno/';
}

$route = str_replace($basePath, '', $requestPath);

// Remove trailing slash and .php extension
$route = rtrim($route, '/');
if (substr($route, -4) === '.php') {
    $route = substr($route, 0, -4);
}

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

    // Movement routes
    elseif ($route === 'index' || $route === '') {
        $controller = new MovementController();
        $controller->index();
    }

    elseif ($route === 'cadastro') {
        $controller = new MovementController();
        $controller->create();
    }

    elseif ($route === 'salvar') {
        $controller = new MovementController();
        $controller->store();
    }

    elseif ($route === 'editar') {
        $controller = new MovementController();
        $controller->edit();
    }

    elseif ($route === 'atualizar') {
        $controller = new MovementController();
        $controller->update();
    }

    elseif ($route === 'pendentes') {
        $controller = new MovementController();
        $controller->pending();
    }

    elseif ($route === 'saida') {
        $controller = new MovementController();
        $controller->recordSaida();
    }

    elseif ($route === 'deletar') {
        $controller = new MovementController();
        $controller->delete();
    }

    elseif ($route === 'cadastrar_rapido') {
        $controller = new ReferenceController();
        $controller->createQuick();
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
