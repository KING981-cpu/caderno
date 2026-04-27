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
        $where = "WHERE m.ativo = 1";
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND (i.patrimonio LIKE :search OR l.nome LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['date'])) {
            $where .= " AND DATE(i.data_entrada) = :date";
            $params['date'] = $filters['date'];
        }

        $orderBy = $filters['orderBy'] ?? 'i.id_itens';
        $direction = ($filters['direction'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT DISTINCT m.*, 
                       GROUP_CONCAT(i.patrimonio) as patrimonios,
                       l.nome as local_nome, 
                       u.nome as usuario_nome
                FROM {$this->table} m
                LEFT JOIN movimentacao_itens i ON m.id_movimentacao = i.movimentacao
                LEFT JOIN localidade l ON m.localidade = l.id_localidade
                LEFT JOIN usuario u ON m.usuario = u.id_usuario
                {$where}
                GROUP BY m.id_movimentacao
                ORDER BY {$orderBy} {$direction}
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCount(array $filters = []): int
    {
        $where = "WHERE i.ativo = 1";
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND (i.patrimonio LIKE :search OR l.nome LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['date'])) {
            $where .= " AND DATE(i.data_entrada) = :date";
            $params['date'] = $filters['date'];
        }

        $sql = "SELECT COUNT(*) as total FROM movimentacao_itens i
                JOIN movimentacao m ON i.movimentacao = m.id_movimentacao
                LEFT JOIN localidade l ON m.localidade = l.id_localidade
                {$where}";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $stmt->execute();

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
