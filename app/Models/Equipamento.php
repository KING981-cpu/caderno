<?php

namespace App\Models;

use App\Core\BaseModel;
use PDO;

class Equipamento extends BaseModel
{
    protected string $table = 'equipamento';
    protected string $primaryKey = 'id';

    public function search(string $query, int $limit = 20, int $offset = 0): array
    {
        $where = '';
        $params = [];

        if (!empty($query)) {
            $where = ' WHERE (numeroSerie LIKE :q OR patrimonio LIKE :q OR codigo LIKE :q) ';
            $params['q'] = "%{$query}%";
        }

        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY dataCadastro DESC LIMIT :offset, :size";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(":{$k}", $v, PDO::PARAM_STR);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':size', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchCount(string $query): int
    {
        $where = '';
        $params = [];

        if (!empty($query)) {
            $where = ' WHERE (numeroSerie LIKE :q OR patrimonio LIKE :q OR codigo LIKE :q) ';
            $params['q'] = "%{$query}%";
        }

        $sql = "SELECT COUNT(*) FROM {$this->table} {$where}";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(":{$k}", $v, PDO::PARAM_STR);
        }
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public function getById(string $id): ?array
    {
        return $this->find($id);
    }

    public function createNew(array $data): string
    {
        $data['id'] = bin2hex(random_bytes(16));
        $data['dataCadastro'] = date('Y-m-d H:i:s');

        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $data['metadata'] = json_encode($data['metadata']);
        }

        return (string)$this->create($data);
    }

    public function setInativo(string $id): bool
    {
        $sql = "UPDATE {$this->table} SET estado = 'inativo' WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
