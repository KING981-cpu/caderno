# 🚀 Deployment Guide - Caderno Digital

## Quick Start (Fresh Clone)

```bash
git clone https://github.com/KING981-cpu/caderno.git
cd caderno
chmod +x setup.sh
./setup.sh
```

Then open http://localhost

---

## What Happens During Setup

The `setup.sh` script:

1. ✅ Creates `.env` file (copied from `.env.example`)
2. ✅ Creates `db_data/` directory for database persistence
3. ✅ Starts Docker containers
4. ✅ Waits for database to be ready
5. ✅ Verifies database tables are initialized
6. ✅ Tests application health

---

## Database Persistence

✅ **Data IS persisted** via Docker volume:
- `db_data/` directory is mounted to `/var/lib/mysql` in the container
- All database changes are saved to `db_data/`
- Schema and sample data are loaded from `database/init.sql` on first run

✅ **`.env` file** (NOT in Git for security):
- Copied from `.env.example` on first setup
- Contains sensitive credentials
- Never committed to repository

---

## Manual Setup (if setup.sh doesn't work)

```bash
# 1. Create .env
cp .env.example .env

# 2. Start containers
docker-compose up -d

# 3. Wait for database
sleep 5

# 4. Verify
docker-compose ps
curl http://localhost
```

---

## Troubleshooting

### "Access denied for user 'root'"
✅ Solution: Run `setup.sh` or manually create `.env`

### "Cannot connect to database"
✅ Check: `docker-compose logs db`
✅ Wait longer: MySQL takes 10-30 seconds to start
✅ Verify: `docker-compose ps`

### Database tables missing
✅ Fix: `docker exec db_caderno mysql -u root -p"${MYSQL_ROOT_PASSWORD}" caderno < database/init.sql`

### Port 80 already in use
✅ Change in `docker-compose.yml`:
```yaml
ports:
  - "8080:80"  # Use 8080 instead
```
Then access at http://localhost:8080

---

## Credentials

**Default user:**
- Username: Admin
- User: `Admin`
- Password: Set via secure environment variable

**Database:**
- Host: db (internal), localhost (external)
- User: root
- Database Password: Set `MYSQL_ROOT_PASSWORD` environment variable (use strong password in production)
- Database: caderno

---

## Production Notes

⚠️ **Before production deployment:**

1. Change default credentials in `.env`
2. Set `APP_ENV=production` in `.env`
3. Set `APP_DEBUG=false` in `.env`
4. Use strong passwords for `MYSQL_ROOT_PASSWORD`
5. Use environment variables for secrets (never hardcode)
6. Implement backup strategy for `db_data/`
7. Consider managed database service (RDS, Cloud SQL, etc.)

---

## Stopping & Cleanup

```bash
# Stop containers (keep data)
docker-compose down

# Stop containers and remove volumes (DELETE DATA)
docker-compose down -v

# View logs
docker-compose logs -f

# Rebuild containers
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

---

**✨ Guaranteed to work!** The setup is fully automated and portable.
