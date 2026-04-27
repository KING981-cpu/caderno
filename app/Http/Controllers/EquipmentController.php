<?php

namespace App\Http\Controllers;

use App\Core\BaseController;
use App\Services\EquipmentService;
use App\SRE\Logger;

class EquipmentController extends BaseController
{
    private EquipmentService $equipmentService;

    public function __construct()
    {
        parent::__construct();
        header('Content-Type: application/json; charset=utf-8');
        $this->equipmentService = new EquipmentService();
    }

    public function list(): void
    {
        $page = max(1, (int)$this->request->get('page', 1));
        $size = max(1, min(100, (int)$this->request->get('size', 20)));
        $q = trim($this->request->get('q', ''));

        $result = $this->equipmentService->search($q, $page, $size);

        Logger::info('Equipment list accessed', ['page' => $page, 'size' => $size, 'query' => $q]);

        echo json_encode($result);
        exit;
    }

    public function get(): void
    {
        $id = $this->request->get('id');
        if (empty($id)) {
            $this->error('id is required', 400);
        }

        $item = $this->equipmentService->getById($id);
        if (!$item) {
            $this->error('Not found', 404);
        }

        echo json_encode($item);
        exit;
    }

    public function store(): void
    {
        if (!$this->request->isPost()) {
            $this->error('Method not allowed', 405);
        }

        try {
            $body = json_decode(file_get_contents('php://input'), true) ?? [];

            $id = $this->equipmentService->create([
                'tipo' => $body['tipo'] ?? null,
                'fabricante' => $body['fabricante'] ?? null,
                'numeroSerie' => $body['numeroSerie'] ?? null,
                'codigo' => $body['codigo'] ?? null,
                'modelo' => $body['modelo'] ?? null,
                'patrimonio' => $body['patrimonio'] ?? null,
                'estado' => $body['estado'] ?? 'disponivel',
                'localId' => $body['localId'] ?? null,
                'responsavelId' => $body['responsavelId'] ?? null,
                'observacoes' => $body['observacoes'] ?? null,
                'metadata' => $body['metadata'] ?? null,
            ]);

            http_response_code(201);
            echo json_encode(['id' => $id]);
            exit;
        } catch (\Exception $e) {
            Logger::error('Equipment creation failed: ' . $e->getMessage());
            $this->error($e->getMessage(), 400);
        }
    }

    public function update(): void
    {
        $id = $this->request->get('id');
        if (empty($id)) {
            $this->error('id is required', 400);
        }

        if (!$this->request->isPut() && !$this->request->isPatch()) {
            $this->error('Method not allowed', 405);
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];

            $success = $this->equipmentService->update($id, $input);

            if ($success) {
                echo json_encode(['ok' => true]);
            } else {
                $this->error('Nothing to update', 400);
            }
            exit;
        } catch (\Exception $e) {
            Logger::error('Equipment update failed: ' . $e->getMessage());
            $this->error($e->getMessage(), 400);
        }
    }

    public function delete(): void
    {
        $id = $this->request->get('id');
        if (empty($id)) {
            $this->error('id is required', 400);
        }

        try {
            $this->equipmentService->delete($id);
            http_response_code(204);
            exit;
        } catch (\Exception $e) {
            Logger::error('Equipment deletion failed: ' . $e->getMessage());
            $this->error($e->getMessage(), 400);
        }
    }
}
