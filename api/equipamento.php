<?php
header('Content-Type: application/json; charset=utf-8');
include __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
if (empty($id)) { http_response_code(400); echo json_encode(['error' => 'id é obrigatório']); exit; }

if ($method === 'GET') {
    $stmt = $pdo->prepare('SELECT * FROM equipamento WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) { http_response_code(404); echo json_encode(['error' => 'Não encontrado']); exit; }
    echo json_encode($row);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

if ($method === 'PUT' || $method === 'PATCH') {
    $fields = [];
    $allowed = ['codigo','tipo','fabricante','modelo','numeroSerie','patrimonio','estado','localId','responsavelId','observacoes','metadata'];
    $params = ['id' => $id];
    foreach ($allowed as $f) {
        if (array_key_exists($f, $input)) {
            $fields[] = "$f = :$f";
            $params[$f] = $f === 'metadata' && is_array($input[$f]) ? json_encode($input[$f]) : $input[$f];
        }
    }
    if (empty($fields)) { http_response_code(400); echo json_encode(['error' => 'Nada para atualizar']); exit; }
    $sql = 'UPDATE equipamento SET ' . implode(', ', $fields) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['ok' => true]);
    exit;
}

if ($method === 'DELETE') {
    // Logical delete: set estado to inativo
    $stmt = $pdo->prepare('UPDATE equipamento SET estado = :estado WHERE id = :id');
    $stmt->execute(['estado' => 'inativo', 'id' => $id]);
    http_response_code(204);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
