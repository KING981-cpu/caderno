<?php

namespace App\Http\Controllers;

use App\Core\BaseController;
use App\Services\ReferenceService;
use App\SRE\Logger;

class ReferenceController extends BaseController
{
    private ReferenceService $referenceService;

    public function __construct()
    {
        parent::__construct();
        $this->referenceService = new ReferenceService();
    }

    public function createQuick(): void
    {
        if (!$this->request->isPost()) {
            $this->error('Invalid request method', 405);
        }

        header('Content-Type: application/json; charset=utf-8');

        $table = $this->request->get('tabela');
        $name = $this->request->get('nome');

        if (empty($table) || empty($name)) {
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        try {
            if ($table === 'localidade') {
                $id = $this->referenceService->createLocalidade($name);
            } elseif ($table === 'usuario') {
                $id = $this->referenceService->createUsuario($name);
            } else {
                throw new \InvalidArgumentException('Invalid table');
            }

            Logger::info('Quick reference created', ['table' => $table, 'id' => $id]);
            echo json_encode(['id' => $id]);
        } catch (\Exception $e) {
            Logger::error('Quick reference creation failed: ' . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}
