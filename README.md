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

El `Dockerfile` en la raíz está optimizado para Railway con PHP 8.3 + Octane (Swoole). Ver `railway.json` y `.env.example`.

## Stack

- Laravel 12 · PHP 8.3
- MySQL · Redis
- Laravel Sanctum · Reverb · Horizon · Octane

## Licencia

MIT
