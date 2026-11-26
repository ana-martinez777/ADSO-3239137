# Sports Gear

Sistema web para administrar los implementos deportivos de un coliseo y controlar los préstamos y devoluciones a deportistas.

El documento del plan de mejoramiento que origina este proyecto está en [`Plan-Mejoramiento-A04.pdf`](Plan-Mejoramiento-A04.pdf).

## Estructura

| Carpeta | Contenido |
|---|---|
| [`docs/`](docs/) | Especificación: `constitution`, `spec`, `plan`, `architecture`, `data-model`, `api-contract`, `tasks` y los ADR en [`docs/adr/`](docs/adr/) |
| [`api/`](api/) | Backend en PHP con Laravel, con arquitectura hexagonal |
| [`app/`](app/) | Frontend en React para administradores y entrenadores |
| [`page/`](page/) | Sitio público estático que presenta el producto |
| [`infra/`](infra/) | Docker Compose: MySQL, API, frontend y sitio público |
| [`tool/`](tool/) | Sembrador de datos de demostración que usa la API |

## Tecnologías

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.4, Laravel 12, JWT, Argon2id |
| Frontend | React 19, Vite, React Router |
| Base de datos | MySQL 8.4 (InnoDB) |
| Infraestructura | Docker Compose, Apache, nginx |
| Sembrador | Node.js 22, sin dependencias externas |

## Ejecución con Docker

Requisitos: Docker Desktop con Docker Compose v2 y los puertos 8000, 8080 y 8081 libres.

1. Crear el archivo de variables a partir de la plantilla y reemplazar cada `change-me`:

   ```bash
   cp infra/.env.example infra/.env
   ```

   | Variable | Descripción |
   |---|---|
   | `DB_PASSWORD`, `DB_ROOT_PASSWORD` | Contraseñas de MySQL |
   | `JWT_SECRET` | Clave para firmar los tokens; mínimo 32 caracteres aleatorios |
   | `APP_KEY` | Clave de Laravel (`base64:...`). Si se deja vacía, se genera una temporal en cada inicio |
   | `SPORTS_GEAR_ADMIN_USERNAME`, `SPORTS_GEAR_ADMIN_PASSWORD` | Administrador inicial |
   | `SEED_COACH_PASSWORD` | Contraseña de los entrenadores de demostración |

   `infra/.env` contiene secretos y no se incluye en el repositorio.

2. Iniciar el sistema con un solo comando. La base de datos inicia vacía; la API espera a MySQL, ejecuta las migraciones y crea las cinco categorías y el administrador:

   ```bash
   docker compose -f infra/docker-compose.yml up --build
   ```

3. Abrir:

   | Dirección | Contenido |
   |---|---|
   | http://localhost:8080 | Aplicación |
   | http://localhost:8081 | Sitio público |
   | http://localhost:8000/api/v1/health | Estado de la API |

Para detener los servicios: `docker compose -f infra/docker-compose.yml down`. Para borrar también la base de datos: agregar `-v`.

## Datos de demostración

Con los servicios en ejecución:

```bash
docker compose -f infra/docker-compose.yml --profile seed run --rm seeder
```

El sembrador usa solo la API y registra 2 entrenadores (`entrenador01` y `entrenador02`, con la contraseña `SEED_COACH_PASSWORD`), 40 implementos, 10 deportistas, 15 préstamos con devoluciones y 3 préstamos vencidos. Si los datos ya existen, no los duplica.

Los préstamos vencidos se registran en una instancia temporal de la API con el reloj 3 días atrás. La decisión está en [ADR-004](docs/adr/ADR-004-sembrado-de-prestamos-vencidos.md).

## Pruebas automatizadas

| Componente | Comando | Contenido |
|---|---|---|
| API | `cd api && php artisan test` | Dominio, API (Feature), arquitectura e integración con MySQL |
| Frontend | `cd app && npm test` | Cliente HTTP, sesión, permisos, validaciones y pantallas |
| Sembrador | `cd tool && npm test` | Datos de demostración y cliente de la API |

Las pruebas de integración (`api/tests/Integration`) se ejecutan contra la base de datos MySQL configurada en `api/.env`, dentro de una transacción que se revierte al terminar. Si no hay conexión a MySQL, se omiten.

## Desarrollo sin Docker

- API: en `api/`, configurar `.env` a partir de `.env.example`, ejecutar `composer install`, `php artisan migrate --seed` y `php artisan serve`.
- Frontend: en `app/`, ejecutar `npm install` y `npm run dev` (http://localhost:5173). Vite reenvía `/api` a `http://127.0.0.1:8000`.

## Usuarios y roles

| Rol | Puede |
|---|---|
| Administrador | Gestionar implementos y entrenadores, registrar deportistas, consultar préstamos y vencidos, ver el reporte de más solicitados |
| Entrenador | Consultar el catálogo activo, registrar deportistas, registrar préstamos y devoluciones, consultar préstamos y vencidos |

Los deportistas no tienen cuenta en el sistema.
