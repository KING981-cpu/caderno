<?php

namespace App\Services;

use App\Models\Equipamento;
use App\SRE\Logger;

class EquipmentService
{
    private Equipamento $equipmentModel;

    public function __construct()
    {
        $this->equipmentModel = new Equipamento();
    }

    public function search(string $query = '', int $page = 1, int $size = 20): array
    {
        $size = max(1, min(100, $size));
        $offset = ($page - 1) * $size;

        try {
            $items = $this->equipmentModel->search($query, $size, $offset);
            $total = $this->equipmentModel->searchCount($query);

            return [
                'items' => $items,
                'total' => $total,
                'page' => $page,
                'size' => $size,
            ];
        } catch (\Exception $e) {
            Logger::error('Equipment search failed: ' . $e->getMessage());
            return ['items' => [], 'total' => 0];
        }
    }

    public function create(array $data): ?string
    {
        try {
            $this->validate($data);
            $id = $this->equipmentModel->createNew($data);
            Logger::info('Equipment created', ['id' => $id]);
            return $id;
        } catch (\Exception $e) {
            Logger::error('Equipment creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update(string $id, array $data): bool
    {
        try {
            $allowed = ['codigo', 'tipo', 'fabricante', 'modelo', 'numeroSerie', 'patrimonio', 'estado', 'localId', 'responsavelId', 'observacoes', 'metadata'];

            $updateData = [];
            foreach ($allowed as $field) {
                if (array_key_exists($field, $data)) {
                    $updateData[$field] = $field === 'metadata' && is_array($data[$field])
                        ? json_encode($data[$field])
                        : $data[$field];
                }
            }

            if (empty($updateData)) {
                return true;
            }

            $success = $this->equipmentModel->update($id, $updateData);
            if ($success) {
                Logger::info('Equipment updated', ['id' => $id]);
            }
            return $success;
        } catch (\Exception $e) {
            Logger::error('Equipment update failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        try {
            $success = $this->equipmentModel->setInativo($id);
            if ($success) {
                Logger::info('Equipment deleted (logical)', ['id' => $id]);
            }
            return $success;
        } catch (\Exception $e) {
            Logger::error('Equipment deletion failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getById(string $id): ?array
    {
        try {
            return $this->equipmentModel->getById($id);
        } catch (\Exception $e) {
            Logger::error('Equipment fetch failed: ' . $e->getMessage());
            return null;
        }
    }

    private function validate(array $data): void
    {
        $required = ['tipo', 'fabricante', 'numeroSerie'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Required field missing: {$field}");
            }
        }
    }
}
