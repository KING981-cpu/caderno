#!/bin/bash

# Caderno Setup Script
# This script sets up the project for development or production

set -e

echo "🚀 Caderno Project Setup"
echo "========================"

# Check if .env exists
if [ ! -f ".env" ]; then
    echo "📝 Creating .env from .env.example..."
    cp .env.example .env
    echo "✅ .env created. Please review and update if needed."
else
    echo "✅ .env already exists"
fi

# Check if db_data exists
if [ ! -d "db_data" ]; then
    echo "📁 Creating db_data directory..."
    mkdir -p db_data
    chmod 777 db_data
    echo "✅ db_data directory created"
else
    echo "✅ db_data directory exists"
fi

# Start Docker containers
echo "🐳 Starting Docker containers..."
docker-compose up -d

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
for i in {1..30}; do
    if docker exec db_caderno mysqladmin ping -h localhost -u root -p"${MYSQL_ROOT_PASSWORD}" &> /dev/null; then
        echo "✅ Database is ready!"
        break
    fi
    if [ $i -eq 30 ]; then
        echo "❌ Database failed to start. Check docker logs:"
        docker-compose logs db
        exit 1
    fi
    echo "  Attempt $i/30..."
    sleep 1
done

# Verify tables were created
echo "🔍 Verifying database tables..."
TABLES=$(docker exec db_caderno mysql -u root -p"${MYSQL_ROOT_PASSWORD}" caderno -e "SHOW TABLES;" 2>/dev/null | wc -l)
if [ "$TABLES" -gt 1 ]; then
    echo "✅ Database tables created successfully!"
else
    echo "⚠️  No tables found. Running manual init..."
    docker exec db_caderno mysql -u root -p"${MYSQL_ROOT_PASSWORD}" caderno < database/init.sql
fi

# Check application health
echo "🏥 Checking application health..."
sleep 5
if curl -f http://localhost/health &> /dev/null; then
    echo "✅ Application is healthy!"
else
    echo "⚠️  Application health check failed. Containers may still be starting."
fi

echo ""
echo "✨ Setup complete!"
echo "🌐 Access the application at: http://localhost"
echo "📚 Database: caderno"
echo "👤 Default user: Admin (use credentials configured in your environment)"
echo ""
echo "To stop: docker-compose down"
echo "To view logs: docker-compose logs -f"
