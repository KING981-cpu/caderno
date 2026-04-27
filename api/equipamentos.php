<?php
header('Content-Type: application/json; charset=utf-8');
include __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $size = max(1, min(100, (int)($_GET['size'] ?? 20)));
    $q = trim($_GET['q'] ?? '');
    $offset = ($page - 1) * $size;

    $where = '';
    $params = [];
    if ($q !== '') {
        $where = ' WHERE (numeroSerie LIKE :q OR patrimonio LIKE :q OR codigo LIKE :q) ';
        $params['q'] = "%$q%";
    }

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM equipamento $where");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $sql = "SELECT * FROM equipamento $where ORDER BY dataCadastro DESC LIMIT :offset, :size";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue(":$k", $v, PDO::PARAM_STR);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':size', $size, PDO::PARAM_INT);
    $stmt->execute();

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['items' => $items, 'total' => $total]);
    exit;
}

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    $required = ['tipo', 'fabricante', 'numeroSerie'];
    foreach ($required as $r) {
        if (empty($body[$r])) {
            http_response_code(400);
            echo json_encode(['error' => "Campo obrigatório: $r"]);
            exit;
        }
    }

    $id = bin2hex(random_bytes(16));
    $sql = "INSERT INTO equipamento (id, codigo, tipo, fabricante, modelo, numeroSerie, patrimonio, estado, localId, responsavelId, dataCadastro, observacoes, metadata)
            VALUES (:id, :codigo, :tipo, :fabricante, :modelo, :numeroSerie, :patrimonio, :estado, :localId, :responsavelId, :dataCadastro, :observacoes, :metadata)";
    $stmt = $pdo->prepare($sql);
    $now = (new DateTime())->format('Y-m-d H:i:s');
    $stmt->execute([
        'id' => $id,
        'codigo' => $body['codigo'] ?? null,
        'tipo' => $body['tipo'],
        'fabricante' => $body['fabricante'],
        'modelo' => $body['modelo'] ?? null,
        'numeroSerie' => $body['numeroSerie'],
        'patrimonio' => $body['patrimonio'] ?? null,
        'estado' => $body['estado'] ?? 'disponivel',
        'localId' => $body['localId'] ?? null,
        'responsavelId' => $body['responsavelId'] ?? null,
        'dataCadastro' => $now,
        'observacoes' => $body['observacoes'] ?? null,
        'metadata' => isset($body['metadata']) ? json_encode($body['metadata']) : null
    ]);

    http_response_code(201);
    echo json_encode(['id' => $id]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
