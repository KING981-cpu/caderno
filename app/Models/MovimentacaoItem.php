<?php

namespace App\Models;

use App\Core\BaseModel;
use PDO;

class MovimentacaoItem extends BaseModel
{
    protected string $table = 'movimentacao_itens';
    protected string $primaryKey = 'id_itens';

    public function getWithDetails($id): ?array
    {
        $sql = "SELECT i.id_itens, i.patrimonio, i.data_entrada, i.data_saida, 
                       m.id_movimentacao, m.tipo, m.localidade, m.usuario, m.observacao 
                FROM {$this->table} i
                JOIN movimentacao m ON i.movimentacao = m.id_movimentacao 
                WHERE i.id_itens = :id LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findByPatrimonio(string $patrimonio): ?array
    {
        $sql = "SELECT i.*, m.tipo, m.assinatura, l.nome as local, u.nome as user
                FROM {$this->table} i
                JOIN movimentacao m ON i.movimentacao = m.id_movimentacao
                LEFT JOIN localidade l ON m.localidade = l.id_localidade
                LEFT JOIN usuario u ON m.usuario = u.id_usuario
                WHERE i.patrimonio = :pat AND i.ativo = 1
                ORDER BY i.id_itens DESC
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['pat' => $patrimonio]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findOpenByPatrimonio(string $patrimonio): ?array
    {
        $sql = "SELECT i.id_itens, i.patrimonio, i.data_entrada, i.data_saida, m.tipo
                FROM {$this->table} i
                JOIN movimentacao m ON i.movimentacao = m.id_movimentacao
                WHERE i.patrimonio = :pat AND i.ativo = 1
                  AND (i.data_saida IS NULL OR i.data_saida = '0000-00-00')
                ORDER BY i.id_itens DESC
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['pat' => $patrimonio]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateDateSaidaById(int $id, string $dataSaida): bool
    {
        $sql = "UPDATE {$this->table}
                SET data_saida = :data
                WHERE id_itens = :id AND ativo = 1";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'data' => $dataSaida,
            'id' => $id
        ]);
    }

    public function createWithMovimentacao(int $movId, string $patrimonio, string $columnDate, string $dateValue): int|string
    {
        $sql = "INSERT INTO {$this->table} (patrimonio, movimentacao, {$columnDate}) 
                VALUES (:pat, :mov, :data_item)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'pat' => $patrimonio,
            'mov' => $movId,
            'data_item' => $dateValue
        ]);

        return $this->pdo->lastInsertId();
    }

    public function updateDateSaida(string $patrimonio, string $dataSaida): bool
    {
        $sql = "UPDATE {$this->table} 
                SET data_saida = :data 
                WHERE patrimonio = :pat AND (data_saida IS NULL OR data_saida = '0000-00-00')";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'data' => $dataSaida,
            'pat' => $patrimonio
        ]);
    }

    public function deactivate($id): bool
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id_itens = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function getDeletedItems(): array
    {
        $sql = "SELECT i.id_itens, i.patrimonio, i.data_entrada, i.data_saida, m.id_movimentacao, m.tipo, m.assinatura, l.nome as local, u.nome as user
                FROM {$this->table} i
                JOIN movimentacao m ON i.movimentacao = m.id_movimentacao
                LEFT JOIN localidade l ON m.localidade = l.id_localidade
                LEFT JOIN usuario u ON m.usuario = u.id_usuario
                WHERE i.ativo = 0
                ORDER BY i.data_entrada DESC";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFullDetails($id): ?array
    {
        $sql = "SELECT i.id_itens, i.patrimonio, i.data_entrada, i.data_saida, 
                       m.id_movimentacao, m.tipo, m.assinatura, m.observacao,
                       l.nome as local, l.id_localidade,
                       u.nome as user, u.id_usuario
                FROM {$this->table} i
                JOIN movimentacao m ON i.movimentacao = m.id_movimentacao
                JOIN localidade l ON m.localidade = l.id_localidade
                JOIN usuario u ON m.usuario = u.id_usuario
                WHERE i.id_itens = :id LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
