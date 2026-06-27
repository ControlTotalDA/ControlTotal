# ControlTotal

Backend API REST multitenant para monitoreo IoT industrial de energía eléctrica (voltaje, corriente, potencia). Laravel 12, Sanctum, Reverb, Horizon y MySQL.

## Requisitos

- Docker Desktop 4.x+
- Git

## Inicio rápido con Docker

```bash
git clone https://github.com/ControlTotalDA/ControlTotal.git
cd ControlTotal

docker compose up -d --build
docker compose exec app php artisan db:seed
```

Servicios:

| Servicio | URL / Puerto |
|----------|----------------|
| API | http://localhost:8000 |
| phpMyAdmin | http://localhost:8081 |
| WebSockets (Reverb) | ws://localhost:8080 |
| MySQL | localhost:3306 |
| Redis | localhost:6379 |

### Credenciales de prueba (seed)

| Tenant | Email | Password |
|--------|-------|----------|
| Empresa A | admin@empresa-a.com | password |
| Empresa B | admin@empresa-b.com | password |

## Comandos útiles

```bash
# Ver logs
docker compose logs -f app

# Ejecutar tests
docker compose exec app php artisan test

# Artisan
docker compose exec app php artisan migrate:fresh --seed

# Detener
docker compose down
```

## API

Base URL: `/api/v1`

- `POST /auth/login` — autenticación (Bearer token)
- `GET /machines`, `POST /metrics` (header `X-API-Key`)
- Documentación de endpoints en los controllers bajo `app/Http/Controllers/Api/V1/`

## Producción (Railway)

El build de Docker **sí funcionó** — lo que falló fue el **healthcheck**: la app no respondió en `/up` porque no pudo arrancar (normalmente falta MySQL o `APP_KEY`).

### Pasos en Railway

1. **Crear servicio MySQL** en el mismo proyecto y vincularlo al servicio de la API.
2. **Variables de entorno** obligatorias:

| Variable | Valor |
|----------|--------|
| `APP_KEY` | Generar con `php artisan key:generate --show` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | URL pública de Railway |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `${{MySQL.MYSQLHOST}}` |
| `DB_PORT` | `${{MySQL.MYSQLPORT}}` |
| `DB_DATABASE` | `${{MySQL.MYSQLDATABASE}}` |
| `DB_USERNAME` | `${{MySQL.MYSQLUSER}}` |
| `DB_PASSWORD` | `${{MySQL.MYSQLPASSWORD}}` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `SESSION_DRIVER` | `database` |

3. Redeploy. El script `docker/railway-start.sh` espera MySQL, migra y arranca Octane.

Ver `Dockerfile` y `railway.json`.

## Stack

- Laravel 12 · PHP 8.3
- MySQL · Redis
- Laravel Sanctum · Reverb · Horizon · Octane

## Licencia

MIT
