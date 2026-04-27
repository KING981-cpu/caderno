# Caderno Digital - API Documentation

## Base URL
```
http://localhost/
```

## Authentication
The application uses session-based authentication. Users must login through the web interface before accessing protected endpoints.

## Endpoints

### Authentication

#### Login
- **URL:** `/autenticar` (POST)
- **Parameters:**
  - `cpf` (string, required): User's CPF
  - `senha` (string, required): User's password
- **Response:** Redirects to index on success, shows alert on failure

#### Logout
- **URL:** `/sair` (GET)
- **Response:** Redirects to login page

### Movements

#### List Movements
- **URL:** `/index` (GET)
- **Parameters:**
  - `pagina` (int): Page number (default: 1)
  - `limite` (int): Items per page (default: 10)
  - `busca` (string): Search term
  - `data_pesquisa` (string): Filter by date (Y-m-d)
  - `ordem` (string): Sort column
  - `direcao` (string): Sort direction (ASC/DESC)
- **Response:** HTML table with paginated results
- **Auth:** Required

#### Create Movement Form
- **URL:** `/cadastro` (GET)
- **Response:** HTML form
- **Auth:** Required

#### Create Movement
- **URL:** `/salvar` (POST)
- **Parameters:**
  - `tipo` (string): "Entrada" or "Saída"
  - `patrimonio` (string): Comma-separated list of equipment IDs
  - `entrada` (string): Date (Y-m-d)
  - `localidade` (int): Location ID
  - `usuario` (int): User ID
  - `assinatura_data` (string): Base64 signature image
- **Response:** Redirects on success
- **Auth:** Required

#### Edit Movement Form
- **URL:** `/editar?id={id}` (GET)
- **Response:** HTML form with prefilled data
- **Auth:** Required

#### Update Movement
- **URL:** `/atualizar` (POST)
- **Parameters:**
  - `id_movimentacao` (int): Movement ID
  - `id_itens` (int): Item ID
  - `tipo` (string): Movement type
  - `patrimonio` (string): Equipment ID
  - `data_entrada` (string): Entry date
  - `localidade` (int): Location ID
  - `usuario` (int): User ID
  - `observacao` (string): Observations
- **Response:** Redirects on success
- **Auth:** Required

#### List Pending Items
- **URL:** `/pendentes` (GET)
- **Response:** HTML table
- **Auth:** Required

#### Record Exit
- **URL:** `/saida?id_item={patrimonio}` (GET/POST)
- **GET Response:** HTML form
- **POST Parameters:**
  - `data_saida` (string): Exit date (Y-m-d)
- **POST Response:** Redirects on success
- **Auth:** Required

#### Delete Item
- **URL:** `/deletar?id_item={id}` (GET)
- **Response:** Redirects on success
- **Auth:** Required

### References

#### Create Quick Reference
- **URL:** `/cadastrar_rapido` (POST)
- **Parameters:**
  - `tabela` (string): "localidade" or "usuario"
  - `nome` (string): Name
- **Response:** JSON `{id: int}` or `{error: string}`
- **Auth:** Required

### Equipment (API)

#### List Equipment
- **URL:** `/api/equipamentos` (GET)
- **Parameters:**
  - `page` (int): Page number (default: 1)
  - `size` (int): Items per page (default: 20, max: 100)
  - `q` (string): Search query
- **Response:** JSON
```json
{
  "items": [...],
  "total": 100
}
```

#### Get Equipment
- **URL:** `/api/equipamento?id={id}` (GET)
- **Response:** JSON equipment object or 404

#### Create Equipment
- **URL:** `/api/equipamentos` (POST)
- **Body:** JSON
```json
{
  "tipo": "string",
  "fabricante": "string",
  "numeroSerie": "string",
  "codigo": "string (optional)",
  "modelo": "string (optional)",
  "patrimonio": "string (optional)",
  "estado": "string (optional)",
  "localId": "int (optional)",
  "responsavelId": "int (optional)",
  "observacoes": "string (optional)",
  "metadata": "object (optional)"
}
```
- **Response:** JSON `{id: string}`

#### Update Equipment
- **URL:** `/api/equipamento?id={id}` (PUT/PATCH)
- **Body:** JSON with fields to update
- **Response:** JSON `{ok: true}`

#### Delete Equipment
- **URL:** `/api/equipamento?id={id}` (DELETE)
- **Response:** 204 No Content

### Health Check

#### Health Status
- **URL:** `/health` (GET)
- **Response:** JSON
```json
{
  "status": "healthy|degraded",
  "timestamp": "2024-04-27T12:00:00",
  "services": {
    "database": {
      "status": "healthy|unhealthy",
      "message": "string"
    }
  }
}
```

## Status Codes
- `200`: Success
- `201`: Created
- `204`: No Content
- `400`: Bad Request
- `404`: Not Found
- `405`: Method Not Allowed
- `500`: Internal Server Error

## Error Response Format
```json
{
  "success": false,
  "error": "Error message"
}
```

## Session Management
Sessions are configured with the following security settings:
- `httponly`: true (prevents JavaScript access)
- `secure`: true (HTTPS only in production)
- `samesite`: Lax (CSRF protection)
