<?php

namespace App\Services;

use App\Models\Movimentacao;
use App\Models\MovimentacaoItem;
use App\SRE\Logger;

class MovementService
{
    private Movimentacao $movModel;
    private MovimentacaoItem $itemModel;

    public function __construct()
    {
        $this->movModel = new Movimentacao();
        $this->itemModel = new MovimentacaoItem();
    }

    public function create(array $data): ?int
    {
        try {
            $pdo = \App\Core\Database::getInstance();
            $pdo->beginTransaction();

            // Create movement
            $movId = $this->movModel->create([
                'observacao' => $data['observacao'] ?? '',
                'localidade' => $data['localidade'] ?? null,
                'usuario' => $data['usuario'] ?? null,
                'tipo' => $data['tipo'] ?? 'Entrada',
                'assinatura' => $data['assinatura'] ?? null,
            ]);

            // Create items
            $columnDate = ($data['tipo'] ?? 'Entrada') === 'Entrada' ? 'data_entrada' : 'data_saida';
            $dateValue = $data['data'] ?? date('Y-m-d');

            $patrimonios = explode(',', $data['patrimonio'] ?? '');
            foreach ($patrimonios as $patrimonio) {
                $patrimonio = trim($patrimonio);
                if (!empty($patrimonio)) {
                    $this->itemModel->createWithMovimentacao($movId, $patrimonio, $columnDate, $dateValue);
                }
            }

            $pdo->commit();
            Logger::info('Movement created', ['id' => $movId]);
            return $movId;
        } catch (\Exception $e) {
            $pdo->rollBack();
            Logger::error('Movement creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update(int $movId, int $itemId, array $data): bool
    {
        try {
            $pdo = \App\Core\Database::getInstance();
            $pdo->beginTransaction();

            $this->movModel->update($movId, [
                'localidade' => $data['localidade'] ?? null,
                'usuario' => $data['usuario'] ?? null,
                'tipo' => $data['tipo'] ?? 'Entrada',
                'observacao' => $data['observacao'] ?? '',
            ]);

            $this->itemModel->update($itemId, [
                'patrimonio' => $data['patrimonio'] ?? '',
                'data_entrada' => $data['data_entrada'] ?? null,
            ]);

            $pdo->commit();
            Logger::info('Movement updated', ['id' => $movId]);
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            Logger::error('Movement update failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function recordSaida(string $patrimonio, string $dataSaida): bool
    {
        try {
            $success = $this->itemModel->updateDateSaida($patrimonio, $dataSaida);
            if ($success) {
                Logger::info('Saida recorded', ['patrimonio' => $patrimonio]);
            }
            return $success;
        } catch (\Exception $e) {
            Logger::error('Saida recording failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getPendingItems(): array
    {
        try {
            return $this->movModel->getPendingItems();
        } catch (\Exception $e) {
            Logger::error('Failed to get pending items: ' . $e->getMessage());
            return [];
        }
    }

    public function list(int $page = 1, int $limit = 10, array $filters = []): array
    {
        try {
            $offset = ($page - 1) * $limit;
            $items = $this->movModel->getAll($limit, $offset, $filters);
            $total = $this->movModel->getTotalCount($filters);

            return [
                'items' => $items,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit),
            ];
        } catch (\Exception $e) {
            Logger::error('Failed to list movements: ' . $e->getMessage());
            return ['items' => [], 'total' => 0, 'page' => $page, 'limit' => $limit, 'pages' => 0];
        }
    }

    public function delete(int $itemId): bool
    {
        try {
            $success = $this->itemModel->deactivate($itemId);
            if ($success) {
                Logger::info('Item deleted', ['id' => $itemId]);
            }
            return $success;
        } catch (\Exception $e) {
            Logger::error('Item deletion failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getItemDetails(int $itemId): ?array
    {
        try {
            return $this->itemModel->getFullDetails($itemId);
        } catch (\Exception $e) {
            Logger::error('Failed to get item details: ' . $e->getMessage());
            return null;
        }
    }
}
