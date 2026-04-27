<?php

namespace App\Models;

use App\Core\BaseModel;
use PDO;

class Movimentacao extends BaseModel
{
    protected string $table = 'movimentacao';
    protected string $primaryKey = 'id_movimentacao';

    public function getWithDetails($id): ?array
    {
        $sql = "SELECT m.*, 
                       l.nome as local_nome, 
                       u.nome as usuario_nome
                FROM {$this->table} m
                LEFT JOIN localidade l ON m.localidade = l.id_localidade
                LEFT JOIN usuario u ON m.usuario = u.id_usuario
                WHERE m.id_movimentacao = :id LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getAll(int $limit = 10, int $offset = 0, array $filters = []): array
    {
        $where = "WHERE i.ativo = 1";
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND (i.patrimonio LIKE ? OR l.nome LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['date'])) {
            $where .= " AND (DATE(i.data_entrada) = ? OR DATE(i.data_saida) = ?)";
            $params[] = $filters['date'];
            $params[] = $filters['date'];
        }

        // Mapping of column names to table-qualified columns for safe ordering
        $orderByMapping = [
            'patrimonio' => 'i.patrimonio',
            'tipo' => 'm.tipo',
            'entrada' => 'i.data_entrada',
            'saida' => 'i.data_saida',
            'local' => 'l.nome',
            'usuario' => 'u.nome',
            'id_itens' => 'i.id_itens',
            'id_movimentacao' => 'm.id_movimentacao',
        ];

        $orderByInput = $filters['orderBy'] ?? 'i.id_itens';
        $orderBy = $orderByMapping[$orderByInput] ?? 'i.id_itens';
        $direction = ($filters['direction'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT DISTINCT m.*, 
                       GROUP_CONCAT(i.patrimonio) as patrimonios,
                       MIN(i.data_entrada) as data_entrada,
                       MIN(i.data_saida) as data_saida,
                       m.assinatura as assinatura,
                       l.nome as local_nome, 
                       u.nome as usuario_nome
                FROM {$this->table} m
                LEFT JOIN movimentacao_itens i ON m.id_movimentacao = i.movimentacao
                LEFT JOIN localidade l ON m.localidade = l.id_localidade
                LEFT JOIN usuario u ON m.usuario = u.id_usuario
                {$where}
                GROUP BY m.id_movimentacao
                ORDER BY {$orderBy} {$direction}
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCount(array $filters = []): int
    {
        $where = "WHERE i.ativo = 1";
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND (i.patrimonio LIKE ? OR l.nome LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['date'])) {
            $where .= " AND (DATE(i.data_entrada) = ? OR DATE(i.data_saida) = ?)";
            $params[] = $filters['date'];
            $params[] = $filters['date'];
        }

        $sql = "SELECT COUNT(*) as total FROM movimentacao_itens i
                JOIN movimentacao m ON i.movimentacao = m.id_movimentacao
                LEFT JOIN localidade l ON m.localidade = l.id_localidade
                {$where}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getPendingItems(): array
    {
        $sql = "SELECT i.patrimonio, i.data_entrada, l.nome as local, u.nome as user
                FROM movimentacao_itens i
                JOIN movimentacao m ON i.movimentacao = m.id_movimentacao
                JOIN localidade l ON m.localidade = l.id_localidade
                JOIN usuario u ON m.usuario = u.id_usuario
                WHERE (i.data_saida IS NULL OR i.data_saida = '0000-00-00')
                AND (i.data_entrada IS NOT NULL AND i.data_entrada != '0000-00-00')
                ORDER BY i.data_entrada ASC";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
