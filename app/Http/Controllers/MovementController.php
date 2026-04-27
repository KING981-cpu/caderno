<?php

namespace App\Http\Controllers;

use App\Core\BaseController;
use App\Services\MovementService;
use App\Services\ReferenceService;
use App\SRE\Logger;

class MovementController extends BaseController
{
    private MovementService $movementService;
    private ReferenceService $referenceService;

    public function __construct()
    {
        parent::__construct();
        $this->movementService = new MovementService();
        $this->referenceService = new ReferenceService();
    }

    public function index(): void
    {
        $page = max(1, (int)$this->request->get('pagina', 1));
        $limit = max(1, min(100, (int)$this->request->get('limite', 10)));
        $search = $this->request->get('busca', '');
        $date = $this->request->get('data_pesquisa', '');

        $filters = [];
        if (!empty($search)) {
            $filters['search'] = $search;
        }
        if (!empty($date)) {
            $filters['date'] = $date;
        }

        $order = $this->request->get('ordem', 'id_itens');
        $direction = $this->request->get('direcao', 'DESC');

        if ($direction !== 'ASC' && $direction !== 'DESC') {
            $direction = 'DESC';
        }

        $filters['orderBy'] = $order;
        $filters['direction'] = $direction;

        $result = $this->movementService->list($page, $limit, $filters);

        Logger::info('Movement list viewed', ['page' => $page, 'limit' => $limit]);

        $data = [
            'movements' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'limit' => $limit,
            'pages' => $result['pages'],
            'search' => $search,
            'date' => $date,
            'order' => $order,
            'direction' => $direction,
        ];

        $this->view('movements/index', $data);
    }

    public function create(): void
    {
        $localidades = $this->referenceService->getLocalidades();
        $usuarios = $this->referenceService->getUsuarios();

        Logger::info('Movement create form accessed');

        $this->view('movements/create', [
            'localidades' => $localidades,
            'usuarios' => $usuarios,
        ]);
    }

    public function store(): void
    {
        if (!$this->request->isPost()) {
            $this->error('Invalid request method', 405);
        }

        try {
            $localidade = $this->request->get('localidade');
            $usuario = $this->request->get('usuario');
            
            // Se vier como array, usa o primeiro
            if (is_array($localidade)) {
                $localidade = !empty($localidade) ? $localidade[0] : null;
            }
            if (is_array($usuario)) {
                $usuario = !empty($usuario) ? $usuario[0] : null;
            }

            $movId = $this->movementService->create([
                'tipo' => $this->request->get('tipo', 'Entrada'),
                'patrimonio' => $this->request->get('patrimonio', ''),
                'data' => $this->request->get('data', date('Y-m-d')),
                'localidade' => $localidade,
                'usuario' => $usuario,
                'assinatura' => $this->request->get('assinatura_data'),
                'observacao' => $this->request->get('observacao', ''),
            ]);

            Logger::info('Movement created via controller', ['id' => $movId]);
            $this->redirect('/');
        } catch (\Exception $e) {
            Logger::error('Movement creation failed: ' . $e->getMessage());
            echo "Erro ao salvar: " . htmlspecialchars($e->getMessage());
        }
    }

    public function edit(): void
    {
        $id = $this->request->get('id');
        if (!$id) {
            $this->redirect('/');
        }

        $item = $this->movementService->getItemDetails($id);
        if (!$item) {
            echo "Registro não encontrado.";
            return;
        }

        $localidades = $this->referenceService->getLocalidades();
        $usuarios = $this->referenceService->getUsuarios();

        Logger::info('Movement edit form accessed', ['id' => $id]);

        $this->view('movements/edit', [
            'item' => $item,
            'localidades' => $localidades,
            'usuarios' => $usuarios,
        ]);
    }

    public function update(): void
    {
        if (!$this->request->isPost()) {
            $this->error('Invalid request method', 405);
        }

        $movId = (int)$this->request->get('id_movimentacao');
        $itemId = (int)$this->request->get('id_itens');

        try {
            $this->movementService->update($movId, $itemId, [
                'tipo' => $this->request->get('tipo', 'Entrada'),
                'patrimonio' => $this->request->get('patrimonio', ''),
                'data_entrada' => $this->request->get('data_entrada'),
                'localidade' => $this->request->get('localidade'),
                'usuario' => $this->request->get('usuario'),
                'observacao' => $this->request->get('observacao', ''),
            ]);

            Logger::info('Movement updated via controller', ['id' => $movId]);
            $this->redirect('/');
        } catch (\Exception $e) {
            Logger::error('Movement update failed: ' . $e->getMessage());
            echo "Erro ao atualizar: " . htmlspecialchars($e->getMessage());
        }
    }

    public function pending(): void
    {
        $items = $this->movementService->getPendingItems();
        Logger::info('Pending items viewed', ['count' => count($items)]);

        $this->view('movements/pending', ['items' => $items]);
    }

    public function recordSaida(): void
    {
        $patrimonio = $this->request->get('id_item');
        if (!$patrimonio) {
            echo "Patrimônio não especificado.";
            return;
        }

        if ($this->request->isPost()) {
            $dataSaida = $this->request->get('data_saida');

            try {
                $this->movementService->recordSaida($patrimonio, $dataSaida);
                Logger::info('Saida recorded via controller', ['patrimonio' => $patrimonio]);
                $this->redirect('/');
            } catch (\Exception $e) {
                Logger::error('Saida recording failed: ' . $e->getMessage());
                echo "Erro ao registrar saída: " . htmlspecialchars($e->getMessage());
            }
        } else {
            $this->view('movements/saida', ['patrimonio' => $patrimonio]);
        }
    }

    public function delete(): void
    {
        $id = (int)$this->request->get('id_item');
        if (!$id) {
            $this->redirect('/');
        }

        try {
            $this->movementService->delete($id);
            Logger::info('Item deleted via controller', ['id' => $id]);
            $this->redirect('/');
        } catch (\Exception $e) {
            Logger::error('Item deletion failed: ' . $e->getMessage());
            echo "Erro ao processar: " . htmlspecialchars($e->getMessage());
        }
    }
}
