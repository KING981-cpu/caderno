<?php

namespace App\Models;

use App\Core\BaseModel;

class Usuario extends BaseModel
{
    protected string $table = 'usuario';
    protected string $primaryKey = 'id_usuario';

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)[0] ?? null;
    }

    public function findByCpf(string $cpf): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE cpf = :cpf LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['cpf' => $cpf]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function getById($id): ?array
    {
        return $this->find($id);
    }

    public function getAllActive(): array
    {
        return $this->where('ativo', 1);
    }
}
