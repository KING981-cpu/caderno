# Caderno Digital - Architecture Guide

## Overview
Caderno Digital has been refactored from a monolithic procedural structure to a clean, layered architecture following design patterns and best practices.

## Directory Structure

```
app/
├── Core/              # Base classes and foundations
│   ├── BaseController.php
│   ├── BaseModel.php
│   ├── Database.php
│   └── Request.php
├── Http/
│   └── Controllers/   # Request handlers
│       ├── AuthController.php
│       ├── EquipmentController.php
│       ├── HealthController.php
│       ├── MovementController.php
│       └── ReferenceController.php
├── Models/           # Data models and database interactions
│   ├── Equipamento.php
│   ├── Localidade.php
│   ├── Movimentacao.php
│   ├── MovimentacaoItem.php
│   └── Usuario.php
├── Services/         # Business logic
│   ├── AuthService.php
│   ├── EquipmentService.php
│   ├── MovementService.php
│   └── ReferenceService.php
└── SRE/             # Site Reliability Engineering layer
    ├── ErrorHandler.php
    ├── HealthCheck.php
    ├── Logger.php
    └── Middleware.php

config/              # Configuration files
├── app.php
├── bootstrap.php
├── database.php
└── logging.php

public/              # Web root
├── .htaccess
└── index.php (Router)

resources/
└── views/           # Template files
    ├── auth/
    ├── includes/
    └── movements/

database/
└── migrations/      # Database migration files

tests/
└── Unit/            # Unit tests

vendor/
└── autoload.php     # PSR-4 autoloader
```

## Layer Descriptions

### Core Layer
Foundation classes providing common functionality:
- **Database**: Singleton PDO connection manager
- **Request**: HTTP request abstraction
- **BaseModel**: Abstract model with CRUD operations
- **BaseController**: Controller base with response helpers

### Models Layer
Models handle data access and persistence:
- Extends BaseModel for CRUD operations
- Implements domain-specific queries
- Uses prepared statements for security
- Returns associative arrays (for compatibility)

### Services Layer
Business logic and application rules:
- Authentication and authorization
- Movement management
- Equipment management
- Reference data management
- No direct database access (uses models)

### Controllers Layer
HTTP request handlers:
- Parse and validate input via Request
- Call services for business logic
- Return responses (JSON/HTML)
- Handle authentication checks

### SRE Layer
Operational and reliability features:
- **Logger**: PSR-3 style structured logging
- **ErrorHandler**: Centralized exception handling
- **HealthCheck**: Service health monitoring
- **Middleware**: Request/response logging

## Data Flow

### Request Flow
```
HTTP Request
    ↓
public/index.php (Router)
    ↓
Controller (parse request)
    ↓
Service (business logic)
    ↓
Model (data access)
    ↓
Database (PDO)
    ↓
Response (JSON/HTML/Redirect)
```

### Create Movement Example
```
POST /salvar
    ↓
MovementController::store()
    ↓
MovementService::create()
    ↓
Movimentacao::create()  (header)
MovimentacaoItem::createWithMovimentacao()  (items)
    ↓
Database queries
    ↓
Redirect to index.php
```

## Security Features

1. **Input Validation**: All inputs validated via Request class
2. **Output Escaping**: `e()` function for XSS prevention
3. **Prepared Statements**: PDO prevents SQL injection
4. **CSRF Protection**: Session SameSite=Lax
5. **Password Hashing**: password_hash() with legacy support
6. **Session Security**: HttpOnly, Secure flags
7. **Logical Deletes**: Items marked inactive instead of deleted

## Configuration

### Environment Variables (.env)
```
DB_HOST=db
DB_NAME=caderno
DB_USER=root
DB_PASS=password
APP_ENV=production
APP_DEBUG=false
TIMEZONE=America/Sao_Paulo
LOG_LEVEL=INFO
LOG_PATH=/var/log/caderno/app.log
```

### Bootstrap Process
1. Load .env file
2. Register PSR-4 autoloader
3. Initialize error handler
4. Set timezone
5. Configure session cookies

## Database Access Pattern

Models provide a consistent interface:
```php
$user = new Usuario();
$userData = $user->findByCpf('12345678900');

$movement = new Movimentacao();
$items = $movement->getAll($limit, $offset, $filters);
$count = $movement->getTotalCount($filters);
```

## Service Injection

Services are instantiated in controllers:
```php
$authService = new AuthService();
$authenticated = $authService->authenticate($cpf, $password);
```

## Testing Considerations

### Unit Tests
- Test services independently
- Mock models with in-memory data
- Test validation logic

### Integration Tests
- Test full request/response cycle
- Use test database

### Example
```php
class AuthServiceTest {
    public function testAuthenticate() {
        $service = new AuthService();
        $user = $service->authenticate('123', 'password');
        $this->assertNotNull($user);
    }
}
```

## Extending the Application

### Adding a New Feature

1. **Create Model** (if data access needed):
```php
namespace App\Models;
class Feature extends BaseModel {
    protected string $table = 'features';
}
```

2. **Create Service** (for business logic):
```php
namespace App\Services;
class FeatureService {
    private Feature $model;
    public function __construct() {
        $this->model = new Feature();
    }
}
```

3. **Create Controller**:
```php
namespace App\Http\Controllers;
class FeatureController extends BaseController {
    private FeatureService $service;
}
```

4. **Add Routes** in public/index.php
5. **Create Views** in resources/views/
6. **Add Tests** in tests/Unit/

## Naming Conventions

- **Classes**: PascalCase (Usuario, MovementService)
- **Methods**: camelCase (findByCpf, createQuick)
- **Properties**: camelCase
- **Constants**: UPPER_SNAKE_CASE
- **Database Tables**: snake_case (movimentacao_itens)
- **Views**: kebab-case (movements/index.php)

## Performance Considerations

1. **Database Indexing**: Add indexes on frequently queried columns
2. **Query Optimization**: Use LIMIT, specific column selection
3. **Caching**: Can add Redis for session/data caching
4. **Pagination**: All list operations paginated
5. **Logging**: Only in SRE layer, doesn't slow down requests

## Future Improvements

1. Add middleware pipeline for cross-cutting concerns
2. Implement event system for async operations
3. Add request/response validation schemas
4. Implement API versioning
5. Add comprehensive API documentation (OpenAPI/Swagger)
6. Implement feature flags
7. Add metrics/monitoring

## Backward Compatibility

The refactored application maintains full backward compatibility:
- All original URLs still work
- All functionality preserved
- Same database schema
- Same user experience

Old direct file access (login.php, cadastro.php, etc.) is routed through public/index.php.
