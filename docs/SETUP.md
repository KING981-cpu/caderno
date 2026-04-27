# Caderno Digital - Setup Guide

## Prerequisites
- Docker and Docker Compose
- PHP 8.2+ (for local development)
- MySQL 5.7+ or MariaDB 10.4+

## Local Development Setup

### 1. Clone and Setup
```bash
git clone <repository>
cd caderno
cp .env.example .env
```

### 2. Configure Environment
Edit `.env` with your settings:
```bash
DB_HOST=db
DB_NAME=caderno
DB_USER=root
DB_PASS=your_secure_password_here
APP_ENV=development
APP_DEBUG=true
```

### 3. Start with Docker
```bash
docker-compose up -d
```

This will:
- Start PHP 8.2 Apache server on port 80
- Start MariaDB on port 3306
- Mount application in `/var/www/html`

### 4. Initialize Database
```bash
# Access the database
docker-compose exec db mysql -u root -p caderno

# Run SQL migrations (if any in database/migrations/)
# or use existing schema
```

### 5. Verify Installation
```bash
# Check health endpoint
curl http://localhost/health

# Login with test account
# URL: http://localhost/login
# CPF: 123
# User: Admin
# Password: Set via secure environment variable
```

## Development Environment

### Project Structure
```
caderno/
├── app/              # Source code
├── config/           # Configuration
├── public/           # Web root
├── resources/views/  # Templates
├── tests/            # Test suite
├── vendor/           # Auto-generated dependencies
├── .env              # Environment variables
├── docker-compose.yml
├── Dockerfile
└── README.md
```

### Code Style
The project follows PSR-12 standards:
```bash
# Check code style (if you have phpcs installed)
phpcs --standard=PSR12 app/
```

### Running Tests
```bash
# Run all tests
php vendor/bin/phpunit

# Run specific test file
php vendor/bin/phpunit tests/Unit/AuthServiceTest.php

# Run with coverage
php vendor/bin/phpunit --coverage-html coverage/
```

### Debugging

#### Enable Debug Mode
In `.env`:
```
APP_ENV=development
APP_DEBUG=true
```

#### View Logs
```bash
# View error logs
docker-compose exec app tail -f /var/log/caderno/app.log

# View Apache logs
docker-compose logs -f app
```

#### Database Access
```bash
# Access MySQL directly
docker-compose exec db mysql -u root -p caderno

# View database queries (slow query log)
docker-compose exec db tail -f /var/log/mysql/slow.log
```

## Production Deployment

### 1. Prepare Environment
```bash
# Copy environment template
cp .env.example .env

# Update with production values
nano .env
```

### 2. Key Configuration for Production
```
APP_ENV=production
APP_DEBUG=false
DB_HOST=production-db-host
DB_NAME=caderno
DB_USER=secure_user
DB_PASS=production_secure_password
LOG_PATH=/var/log/caderno/app.log
TIMEZONE=America/Sao_Paulo
```

### 3. Docker Build
```bash
# Build production image
docker build -t caderno:latest .

# Or use docker-compose
docker-compose -f docker-compose.prod.yml up -d
```

### 4. Database Migrations
```bash
# Run any pending migrations
docker-compose exec app php -r "require 'database/migrations/init.php';"
```

### 5. Set Permissions
```bash
# Ensure proper permissions
docker-compose exec app chown -R www-data:www-data /var/log/caderno
```

### 6. Health Check
```bash
curl https://yourdomain.com/health
```

## Database Schema

The application uses existing tables:
- `usuario` - System users
- `localidade` - Locations
- `movimentacao` - Movement records
- `movimentacao_itens` - Movement items
- `equipamento` - Equipment registry

### Schema Backup
```bash
# Export database
docker-compose exec db mysqldump -u root -p caderno > backup.sql

# Import database
docker-compose exec db mysql -u root -p caderno < backup.sql
```

## Maintenance

### Regular Tasks

#### Backup Database
```bash
# Daily backup script
docker-compose exec db mysqldump -u root -p caderno | gzip > db_backup_$(date +%Y%m%d).sql.gz
```

#### Check Logs
```bash
# Monitor application logs
docker-compose logs -f app
```

#### Update Dependencies
```bash
# Check for security updates
docker-compose exec app composer update
```

### Troubleshooting

#### Port Already in Use
```bash
# Change port in docker-compose.yml
ports:
  - "8080:80"  # Use 8080 instead of 80
```

#### Database Connection Error
```bash
# Verify database is running
docker-compose exec db mysqladmin ping

# Check connection parameters in .env
```

#### Permission Denied Errors
```bash
# Fix file permissions
docker-compose exec app chown -R www-data:www-data /var/www/html
docker-compose exec app chmod -R 755 /var/www/html
```

#### Session Issues
```bash
# Clear session files
docker-compose exec app rm -rf /var/lib/php/sessions/*
```

## SSL/HTTPS Configuration

### Let's Encrypt with Docker
```bash
# Use certbot container
docker-compose exec app certbot certonly --webroot -w /var/www/html -d yourdomain.com

# Update Apache config with SSL
nano /etc/apache2/sites-available/default-ssl.conf
```

## Performance Optimization

### Database Optimization
```sql
-- Add indexes for better query performance
CREATE INDEX idx_patrimonio ON movimentacao_itens(patrimonio);
CREATE INDEX idx_data_entrada ON movimentacao_itens(data_entrada);
CREATE INDEX idx_usuario ON movimentacao(usuario);
```

### Caching
```php
// Can add Redis for session caching
// Implement in future versions
```

### Asset Optimization
- Minify CSS/JavaScript
- Use CDN for static assets
- Enable GZIP compression in Apache

## Support and Documentation

- **API Docs**: See `docs/API.md`
- **Architecture**: See `docs/ARCHITECTURE.md`
- **Code Style**: PSR-12 standard
- **Test Suite**: `tests/Unit/`

## Common Commands

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f

# Execute command in container
docker-compose exec app php script.php

# Rebuild container
docker-compose build --no-cache

# Access application
http://localhost/

# Access database
docker-compose exec db mysql -u root -p caderno
```

## Security Best Practices

1. **Keep PHP Updated**: Always use PHP 8.2+
2. **Update Dependencies**: Regular composer updates
3. **Secure Passwords**: Use strong database passwords
4. **HTTPS**: Enable SSL/TLS in production
5. **Firewall**: Restrict database port access
6. **Backups**: Regular automated backups
7. **Monitoring**: Monitor logs and errors
8. **Access Control**: Proper user permissions

## Reporting Issues

When reporting issues, include:
- Environment details (PHP, MySQL versions)
- Error logs from `/var/log/caderno/app.log`
- Steps to reproduce
- Expected vs actual behavior
