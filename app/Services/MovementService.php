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
        $patrimonios = array_filter(array_map('trim', explode(',', $data['patrimonio'] ?? '')));
        if (empty($patrimonios)) {
            throw new \Exception('Informe ao menos um patrimônio.');
        }

        if (count($patrimonios) !== count(array_unique($patrimonios))) {
            throw new \Exception('Há patrimônios duplicados na mesma entrada.');
        }

        $tipo = $data['tipo'] ?? 'Entrada';
        $dateValue = $data['data'] ?? date('Y-m-d');

        foreach ($patrimonios as $patrimonio) {
            $last = $this->itemModel->findByPatrimonio($patrimonio);
            if ($last) {
                if ($tipo === 'Entrada') {
                    if (empty($last['data_saida']) || $last['data_saida'] === '0000-00-00') {
                        throw new \Exception("O patrimônio '{$patrimonio}' ainda não saiu. Registre a saída antes da nova entrada.");
                    }
                    if (!empty($last['data_saida']) && $last['data_saida'] > $dateValue) {
                        throw new \Exception("A entrada do patrimônio '{$patrimonio}' não pode ser anterior à última saída ({$last['data_saida']}).");
                    }
                } else {
                    if (empty($last['data_entrada']) || $last['data_entrada'] === '0000-00-00') {
                        throw new \Exception("Não há registro de entrada pendente para o patrimônio '{$patrimonio}'.");
                    }
                    if (!empty($last['data_saida']) && $last['data_saida'] !== '0000-00-00') {
                        throw new \Exception("O patrimônio '{$patrimonio}' já tem saída registrada. Registre uma nova entrada antes de uma nova saída.");
                    }
                    if ($last['data_entrada'] > $dateValue) {
                        throw new \Exception("A saída do patrimônio '{$patrimonio}' não pode ser anterior à entrada ({$last['data_entrada']}).");
                    }
                }
            }
        }

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

            foreach ($patrimonios as $patrimonio) {
                $this->itemModel->createWithMovimentacao($movId, $patrimonio, $columnDate, $dateValue);
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
        $dateObj = \DateTime::createFromFormat('Y-m-d', $dataSaida);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $dataSaida) {
            throw new \Exception('Data de saída inválida.');
        }

        $item = $this->itemModel->findOpenByPatrimonio($patrimonio);
        if (!$item) {
            throw new \Exception('Não há registro de entrada aberto para este patrimônio.');
        }

        if (!empty($item['data_entrada']) && $item['data_entrada'] > $dataSaida) {
            throw new \Exception('A saída não pode ser anterior à data de entrada.');
        }

        try {
            $success = $this->itemModel->updateDateSaidaById((int)$item['id_itens'], $dataSaida);
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

    public function getDeletedItems(): array
    {
        try {
            return $this->itemModel->getDeletedItems();
        } catch (\Exception $e) {
            Logger::error('Failed to get deleted items: ' . $e->getMessage());
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
