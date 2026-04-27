<?php

namespace App\Models;

use App\Core\BaseModel;
use PDO;

class Localidade extends BaseModel
{
    protected string $table = 'localidade';
    protected string $primaryKey = 'id_localidade';

    public function getAllOrderedByName(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY nome ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByName(string $name): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE LOWER(nome) = LOWER(:nome) LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['nome' => $name]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
