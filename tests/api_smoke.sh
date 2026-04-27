#!/usr/bin/env bash
set -e
BASE=${BASE_URL:-"http://localhost"}

echo "Smoke test: listar equipamentos"
curl -s "$BASE/api/equipamentos.php" | jq . || true

echo "Smoke test: criar equipamento (sem persistência em produção)"
resp=$(curl -s -X POST "$BASE/api/equipamentos.php" -H 'Content-Type: application/json' -d '{"tipo":"notebook","fabricante":"Test","numeroSerie":"SN-TEST-001"}')
echo "$resp" | jq . || true

echo "Smoke tests concluídos"
