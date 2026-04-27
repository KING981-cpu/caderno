# Caderno Digital - Sistema de Movimentação de Equipamentos

Uma aplicação web moderna para gerenciamento e rastreamento de movimentações de equipamentos, com arquitetura limpa em camadas, autorização por sessão, e assinatura digital.

## 🚀 Características Principais

- ✅ **Autenticação Segura**: Suporte a hashing de senhas com password_hash() e migrações de legado
- ✅ **Movimentações**: Registrar entrada/saída de equipamentos com assinatura digital
- ✅ **Pesquisa Avançada**: Filtro por patrimônio, localidade, data e paginação
- ✅ **Itens Pendentes**: Visualizar equipamentos ainda em trânsito
- ✅ **API RESTful**: Endpoints para gerenciamento de equipamentos
- ✅ **Health Check**: Monitoramento de saúde da aplicação
- ✅ **Logging Estruturado**: PSR-3 compatible logging
- ✅ **Docker Ready**: Dockerfile otimizado para produção
- ✅ **Segurança**: Prepared statements, XSS prevention, CSRF protection
- ✅ **Testes**: Suite de testes unitários

## 📋 Pré-requisitos

- Docker e Docker Compose
- PHP 8.2+ (para desenvolvimento local)
- MySQL 5.7+ ou MariaDB 10.4+

## 🔧 Setup Rápido

### 1. Clonar Repositório
```bash
git clone <repository>
cd caderno
```

### 2. Configurar Ambiente
```bash
cp .env.example .env
# Editar .env com suas configurações (optional)
```

### 3. Iniciar com Docker
```bash
docker-compose up -d
```

### 4. Acessar Aplicação
```
http://localhost/
```

**Credenciais de Teste:**
- CPF: seu_cpf_de_teste
- Senha: sua_senha_de_teste

## 📁 Estrutura de Diretórios

```
caderno/
├── app/                      # Código fonte
│   ├── Core/                 # Classes base (BaseModel, BaseController, Database, Request)
│   ├── Http/Controllers/    # Controladores (Auth, Movement, Equipment, Health, Reference)
│   ├── Models/              # Modelos de dados (Usuario, Movimentacao, etc)
│   ├── Services/            # Lógica de negócio (Auth, Movement, Equipment, Reference)
│   └── SRE/                 # Site Reliability Engineering (Logger, ErrorHandler, HealthCheck, Middleware)
├── config/                   # Configurações (app, database, logging, bootstrap)
├── database/                 # Migrações de banco de dados
├── public/                   # Raiz web (index.php, .htaccess)
├── resources/views/         # Templates/Views
│   ├── auth/
│   ├── movements/
│   └── includes/
├── tests/                    # Testes unitários
├── vendor/                   # Autoloader PSR-4
├── docs/                     # Documentação (API, ARCHITECTURE, SETUP)
├── .env.example              # Variáveis de ambiente de exemplo
├── Dockerfile                # Configuração Docker
├── docker-compose.yml        # Orquestração de containers
└── README.md                 # Este arquivo
```

## 🏗️ Arquitetura

A aplicação segue o padrão de camadas:

```
HTTP Request → Router → Controller → Service → Model → Database
     ↑
     └── SRE Layer (Logging, Error Handling, Monitoring)
```

### Componentes

- **Controllers**: Manipulam requisições HTTP e chamam services
- **Services**: Contêm lógica de negócio reutilizável
- **Models**: Acesso a dados com prepared statements
- **Core**: Classes base para Controllers e Models
- **SRE**: Logging, tratamento de erros, health checks, middleware

## 📚 Documentação

- **[API.md](docs/API.md)** - Documentação completa dos endpoints
- **[ARCHITECTURE.md](docs/ARCHITECTURE.md)** - Guia de arquitetura e extensão
- **[SETUP.md](docs/SETUP.md)** - Guia de setup e deployment

## 🔌 Endpoints Principais

### Web UI
- `GET /` - Dashboard com listagem de movimentações
- `GET /login` - Página de login
- `POST /autenticar` - Autenticar usuário
- `GET /sair` - Logout
- `GET /cadastro` - Formulário de novo registro
- `POST /salvar` - Salvar nova movimentação
- `GET /editar?id={id}` - Formulário de edição
- `POST /atualizar` - Atualizar movimentação
- `GET /pendentes` - Itens pendentes
- `GET /saida` - Registrar saída

### API REST
- `GET /api/equipamentos` - Listar equipamentos
- `GET /api/equipamento?id={id}` - Obter equipamento
- `POST /api/equipamentos` - Criar equipamento
- `PUT /api/equipamento?id={id}` - Atualizar equipamento
- `DELETE /api/equipamento?id={id}` - Deletar equipamento (logical delete)

### Monitoring
- `GET /health` - Status de saúde da aplicação

## 🔒 Segurança

- ✅ **SQL Injection**: Prepared statements em todas as queries
- ✅ **XSS**: Função `e()` para escape de output
- ✅ **CSRF**: Cookies com SameSite=Lax
- ✅ **Senhas**: password_hash() com suporte a legado
- ✅ **Sessões**: HttpOnly, Secure, SameSite flags
- ✅ **Deletes**: Soft deletes (logical deletes)

## 🧪 Testes

```bash
# Rodar todos os testes
docker-compose exec app php vendor/bin/phpunit

# Teste específico
docker-compose exec app php vendor/bin/phpunit tests/Unit/AuthServiceTest.php

# Com cobertura
docker-compose exec app php vendor/bin/phpunit --coverage-html coverage/
```

## 📊 Banco de Dados

Tabelas principais:
- `usuario` - Usuários do sistema
- `localidade` - Localizações
- `movimentacao` - Registro de movimentações
- `movimentacao_itens` - Itens de uma movimentação
- `equipamento` - Cadastro de equipamentos

## 🐳 Comandos Docker

```bash
# Iniciar serviços
docker-compose up -d

# Parar serviços
docker-compose down

# Ver logs
docker-compose logs -f app

# Acessar banco de dados
docker-compose exec db mysql -u root -p caderno

# Executar comando no container
docker-compose exec app php script.php

# Rebuild imagem
docker-compose build --no-cache
```

## 🌍 Variáveis de Ambiente

```env
# Database
DB_HOST=db
DB_NAME=caderno
DB_USER=root
DB_PASS=your_password

# Application
APP_ENV=production
APP_DEBUG=false
APP_NAME="Caderno Digital"
APP_VERSION=2.0.0

# Timezone
TIMEZONE=America/Sao_Paulo

# Logging
LOG_LEVEL=INFO
LOG_PATH=/var/log/caderno/app.log

# MySQL
MYSQL_ROOT_PASSWORD=your_password
MYSQL_DATABASE=caderno
```

## 📈 Performance

- Paginação em todas as listas
- Queries otimizadas com índices
- Lazy loading de dados
- Session-based caching ready

## 🔄 Migração do Sistema Anterior

Toda a funcionalidade do sistema anterior foi preservada:
- ✅ Mesmo banco de dados
- ✅ Mesmas URLs (routed através de public/index.php)
- ✅ Mesma interface visual
- ✅ 100% compatibilidade

## 🚀 Deployment em Produção

1. Clonar repositório
2. Configurar `.env` com valores de produção
3. `docker-compose -f docker-compose.prod.yml up -d`
4. Verificar `/health` endpoint

Veja [docs/SETUP.md](docs/SETUP.md) para detalhes completos.

## 🐛 Troubleshooting

### Erro de conexão com banco
```bash
# Verificar se o container db está rodando
docker-compose ps

# Ver logs do database
docker-compose logs db
```

### Permissões negadas
```bash
# Corrigir permissões
docker-compose exec app chown -R www-data:www-data /var/www/html
```

### Porta 80 em uso
Editar `docker-compose.yml`:
```yaml
ports:
  - "8080:80"  # Usar porta 8080
```

## 📝 Padrões de Código

- **PSR-12**: PHP Style Guide
- **PSR-4**: Autoloading
- **PSR-3**: Logging Interface

## 📞 Suporte

Para issues ou dúvidas, consulte a documentação em `docs/` ou crie uma issue no repositório.

## 📄 Licença

Copyright 2024 - Caderno Digital

## ✨ Melhorias Futuras

- [ ] Middleware pipeline
- [ ] Event system
- [ ] API authentication (JWT)
- [ ] OpenAPI/Swagger documentation
- [ ] Redis caching
- [ ] Feature flags
- [ ] Metrics/monitoring
- [ ] Two-factor authentication
- [ ] Role-based access control (RBAC)

---

**Versão:** 2.0.0  
**PHP:** 8.2+  
**Database:** MariaDB 10.4+