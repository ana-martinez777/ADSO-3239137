# Sports Gear — Aplicación web (React)

Interfaz para administradores y entrenadores del coliseo. Consume la API Laravel definida en `docs/api-contract.md` (prefijo `/api/v1`) y corresponde a la tarea **T-22** de `docs/tasks.md`.

## Requisitos

- Node.js 20 o superior y npm.
- La API de `api/` en ejecución (por defecto en `http://127.0.0.1:8000`).

## Ejecución en desarrollo

```bash
cd app
cp .env.example .env      # opcional: solo si la API no está en 127.0.0.1:8000
npm install
npm run dev               # http://localhost:5173
```

Vite redirige las peticiones `/api/*` hacia `VITE_API_PROXY_TARGET`. El navegador trabaja con un único origen, así que en desarrollo no se necesita configurar CORS en Laravel.

| Variable | Uso | Valor por defecto |
|---|---|---|
| `VITE_API_BASE_URL` | Prefijo de la API usado por el navegador | `/api/v1` |
| `VITE_API_PROXY_TARGET` | Dirección del backend para el proxy de desarrollo | `http://127.0.0.1:8000` |

## Pruebas y compilación

```bash
npm test          # Vitest + Testing Library
npm run build     # genera dist/
```

## Estructura

```
src/
├── api/          Cliente HTTP único y operaciones del contrato
├── auth/         Sesión, almacenamiento del token y contexto de autenticación
├── config/       Permisos por rol (tabla 8.3 del contrato)
├── components/   Layout, ruta protegida y componentes visuales
├── hooks/        Consulta de datos y resolución de nombres
├── validation/   Validaciones complementarias de formularios
├── utils/        Formato de fechas, estados y mensajes
└── pages/        Pantallas de la aplicación
```

Los componentes visuales no llaman a `fetch` directamente: toda la comunicación pasa por `src/api/` (sección 13 de `docs/architecture.md`).

## Pantallas por rol

| Pantalla | Ruta | Admin | Coach | Historia |
|---|---|---|---|---|
| Inicio de sesión | `/login` | Sí | Sí | HU-01 |
| Catálogo (consulta, búsqueda, filtros) | `/equipment` | Sí | Sí (solo activos) | HU-04 |
| Registrar, editar y dar de baja implementos | `/equipment` | Sí | No | HU-03 |
| Deportistas y detalle | `/athletes`, `/athletes/:id` | Sí | Sí | HU-05 |
| Entrenadores | `/coaches` | Sí | No | HU-02 |
| Registrar préstamo | `/loans/new` | No | Sí | HU-06 |
| Préstamos y detalle | `/loans`, `/loans/:id` | Sí | Sí | HU-08 |
| Registrar devolución (desde el detalle) | `/loans/:id` | No | Sí | HU-07 |
| Vencidos | `/overdue` | Sí | Sí | HU-09 |
| Más solicitados | `/reports/most-requested` | Sí | No | HU-10 |

La interfaz oculta las opciones que el rol no puede usar, pero la autorización real la aplica la API (`FORBIDDEN_ROLE`).

## Manejo del token

- El JWT de `POST /auth/login` se guarda en `sessionStorage` junto con su expiración (`expires_in`, 8 horas). Se borra al cerrar la pestaña.
- La contraseña nunca se guarda.
- La sesión se cierra automáticamente al vencer el token o cuando la API responde `401`.
- Todas las peticiones protegidas envían `Authorization: Bearer <token>`.

## Validaciones

Las validaciones del frontend son complementarias (por ejemplo: máximo 3 unidades, implementos sin repetir, rango de fechas). Las reglas de negocio las garantiza el backend; los errores que devuelve (`ATHLETE_HAS_OVERDUE_LOANS`, `EQUIPMENT_NOT_AVAILABLE`, `REFERENCE_ALREADY_EXISTS`, etc.) se muestran con su mensaje en español.

## Limitaciones conocidas

- `GET /loans` devuelve `athlete_id` y `user_id`. Los nombres se resuelven con `GET /athletes` y, para el administrador, con `GET /users/coaches`. Como el contrato reserva la consulta de entrenadores al administrador, un entrenador ve «Tú» en sus propios préstamos y un identificador corto en los de otros entrenadores.
