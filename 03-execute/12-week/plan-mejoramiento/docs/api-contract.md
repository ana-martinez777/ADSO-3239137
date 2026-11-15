# Contrato de la API — Sports Gear

## 1. Propósito
Este documento define el contrato HTTP de Sports Gear, incluyendo las convenciones de comunicación, autenticación, autorización, endpoints, parámetros, estructuras de petición, respuestas y errores.
El contrato es independiente de la implementación tecnológica. El backend utiliza PHP con Laravel y debe respetar las operaciones y estructuras definidas en este documento.
La especificación funcional se encuentra en `spec.md` y el modelo de persistencia en `data-model.md`.

## 2. Convenciones generales
| Tema | Definición |
|---|---|
| Base | `/api/v1` |
| Formato | JSON UTF-8 |
| Nombres de campos | `snake_case` |
| Identificadores | UUID v4 representados como texto |
| Fechas | ISO 8601 en UTC con indicador `Z` |
| Autenticación | Bearer Token |
| Paginación | No se utiliza |
| Idioma | Mensajes para el usuario en español; campos, rutas y códigos en inglés |

Todas las rutas, excepto `POST /auth/login` y `GET /health`, requieren autenticación.
Las colecciones se devuelven sin paginación.

## 3. Estructura de respuestas

### 3.1 Respuesta de un recurso
Las respuestas que contienen un recurso individual utilizan la propiedad `data`.

Ejemplo de respuesta:
    {
      "data": {
        "id": "uuid",
        "username": "entrenador01",
        "role": "coach"
      }
    }

### 3.2 Respuesta de una colección
Las respuestas que contienen colecciones utilizan `data` como arreglo.

Ejemplo de respuesta:
    {
      "data": []
    }

Cuando no existen resultados, se devuelve un arreglo vacío.

### 3.3 Respuesta sin contenido
Las operaciones que no necesitan devolver información utilizan HTTP 204 No Content.

## 4. Códigos de estado HTTP
| Código | Significado | Uso |
|---|---|---|
| 200 | OK | Operación realizada correctamente |
| 201 | Created | Recurso creado correctamente |
| 204 | No Content | Operación completada sin contenido de respuesta |
| 401 | Unauthorized | Token ausente, inválido o vencido; credenciales incorrectas |
| 403 | Forbidden | El usuario autenticado no tiene permisos suficientes |
| 404 | Not Found | El recurso solicitado no existe o está dado de baja |
| 409 | Conflict | Conflicto con el estado actual del recurso o una regla del negocio |
| 422 | Unprocessable Entity | Datos inválidos o incumplimiento de validaciones de entrada |

## 5. Estructura uniforme de errores
Todos los errores HTTP utilizan la misma estructura:

    {
      "code": "EQUIPMENT_NOT_AVAILABLE",
      "message": "No hay unidades disponibles del implemento solicitado.",
      "details": {}
    }

La propiedad `details` siempre está presente. Cuando no existen detalles adicionales, su valor será un objeto vacío.
Los errores no deben incluir:
- Trazas de ejecución.
- Consultas SQL.
- Rutas internas del servidor.
- Contraseñas.
- Hashes de contraseñas.
- Información sensible de configuración.

## 6. Códigos de error de negocio
| Código | HTTP | Descripción |
|---|---|---|
| `INVALID_CREDENTIALS` | 401 | Las credenciales de acceso son incorrectas |
| `UNAUTHENTICATED` | 401 | No existe una autenticación válida |
| `FORBIDDEN_ROLE` | 403 | El rol no tiene permiso para ejecutar la operación |
| `NOT_FOUND` | 404 | El recurso solicitado no existe o está dado de baja |
| `USERNAME_ALREADY_EXISTS` | 409 | El nombre de usuario ya está registrado |
| `DOCUMENT_NUMBER_ALREADY_EXISTS` | 409 | El documento del deportista ya está registrado |
| `REFERENCE_ALREADY_EXISTS` | 409 | La referencia ya fue utilizada anteriormente y permanece reservada permanentemente |
| `CATEGORY_NOT_FOUND` | 404 | La categoría solicitada no existe |
| `EQUIPMENT_NOT_AVAILABLE` | 409 | No existen unidades disponibles o se produjo un conflicto de disponibilidad |
| `EQUIPMENT_INACTIVE` | 409 | El implemento está dado de baja y no puede utilizarse en nuevos préstamos |
| `ATHLETE_HAS_OVERDUE_LOANS` | 409 | El deportista tiene préstamos vencidos |
| `ATHLETE_LIMIT_EXCEEDED` | 422 | El préstamo supera el máximo de tres unidades pendientes |
| `DUPLICATE_EQUIPMENT_IN_LOAN` | 422 | El mismo implemento aparece más de una vez en un préstamo |
| `LOAN_ITEM_ALREADY_RETURNED` | 409 | El detalle del préstamo ya fue devuelto |
| `VALIDATION_ERROR` | 422 | Uno o varios datos de entrada no cumplen las validaciones |
| `INVALID_DATE_RANGE` | 422 | La fecha inicial es posterior a la fecha final |

## 7. Endpoints
Todas las rutas de esta sección se expresan de forma relativa al prefijo `/api/v1`. Para obtener la ruta completa, se debe anteponer dicho prefijo.

### 7.1 Autenticación y salud

| Método | Ruta relativa | Acceso | Descripción |
|---|---|---|---|
| POST | `/auth/login` | Público | Autentica un usuario y devuelve un token |
| GET | `/health` | Público | Comprueba el estado de la API |

#### POST `/auth/login`
Petición:

    {
      "username": "entrenador01",
      "password": "********"
    }

Respuesta HTTP 200:

    {
      "data": {
        "access_token": "eyJ...",
        "token_type": "Bearer",
        "expires_in": 28800,
        "user": {
          "id": "uuid",
          "username": "entrenador01",
          "role": "coach"
        }
      }
    }

Las credenciales incorrectas generan `INVALID_CREDENTIALS`.
La respuesta nunca incluye la contraseña ni su hash.

#### GET `/health`
Respuesta HTTP 200:

    {
      "status": "ok"
    }

### 7.2 Usuarios y entrenadores
| Método | Ruta | Rol | Descripción |
|---|---|---|---|
| POST | `/users/coaches` | admin | Registra un entrenador |
| GET | `/users/coaches` | admin | Consulta los entrenadores registrados |

#### POST `/users/coaches`
Petición:

    {
      "username": "entrenador01",
      "password": "********"
    }

Respuesta HTTP 201:

    {
      "data": {
        "id": "uuid",
        "username": "entrenador01",
        "role": "coach"
      }
    }

Reglas:
- El nombre de usuario es obligatorio y único.
- Se eliminan los espacios externos y se convierte a minúsculas.
- La contraseña se almacena mediante un mecanismo de hash seguro.
- El rol asignado es `coach`.
- La API no permite crear administradores.
- La contraseña y su hash nunca se devuelven.

#### GET `/users/coaches`
Parámetro opcional:

| Parámetro | Descripción |
|---|---|
| `search` | Busca entrenadores por nombre de usuario |

Respuesta HTTP 200:

    {
      "data": [
        {
          "id": "uuid",
          "username": "entrenador01",
          "role": "coach"
        }
      ]
    }

### 7.3 Categorías
| Método | Ruta | Rol | Descripción |
|---|---|---|---|
| GET | `/categories` | admin, coach | Consulta las categorías disponibles |

Las categorías son fijas y se crean mediante el sembrado inicial de datos.
Las cinco categorías son:
- Balones.
- Raquetas.
- Protección.
- Atletismo.
- Gimnasio.

No se contemplan operaciones para crear, editar o eliminar categorías desde la API.

Respuesta HTTP 200:

    {
      "data": [
        {
          "id": "uuid",
          "name": "Balones"
        }
      ]
    }

### 7.4 Implementos deportivos
| Método | Ruta | Rol | Descripción |
|---|---|---|---|
| GET | `/equipment` | admin, coach | Lista y busca implementos |
| GET | `/equipment/{id}` | admin, coach | Consulta el detalle de un implemento |
| POST | `/equipment` | admin | Registra un implemento |
| PUT | `/equipment/{id}` | admin | Actualiza un implemento |
| DELETE | `/equipment/{id}` | admin | Realiza la baja lógica |

#### GET `/equipment`
Parámetros opcionales:

| Parámetro | Descripción |
|---|---|
| `search` | Busca por referencia del implemento |
| `category_id` | Filtra por identificador de categoría |
| `status` | Filtra por estado: `active`, `inactive` o `all` |

Reglas de consulta:
- Si no se envía `status`, se consultan únicamente implementos activos.
- El administrador puede consultar `active`, `inactive` y `all`.
- El entrenador solamente puede consultar `active`.
- Si el entrenador solicita `inactive` o `all`, la API responde HTTP 403 `FORBIDDEN_ROLE`.
- Los implementos inactivos nunca pueden utilizarse en nuevos préstamos.

Respuesta HTTP 200:

    {
      "data": [
        {
          "id": "uuid",
          "reference": "BAL-001",
          "category": {
            "id": "uuid",
            "name": "Balones"
          },
          "total_units": 10,
          "available_units": 7,
          "deleted_at": null
        }
      ]
    }

#### GET `/equipment/{id}`
Consulta un implemento por su identificador.

Reglas:
- Si el implemento no existe, responde HTTP 404 `NOT_FOUND`.
- El administrador puede consultar implementos activos e inactivos.
- El entrenador solamente puede consultar implementos activos.
- Si un entrenador solicita un implemento inactivo, la API responde HTTP 404 `NOT_FOUND`.

#### POST `/equipment`
Petición:

    {
      "reference": "BAL-001",
      "category_id": "uuid",
      "total_units": 10
    }

Respuesta HTTP 201:

    {
      "data": {
        "id": "uuid",
        "reference": "BAL-001",
        "category_id": "uuid",
        "total_units": 10,
        "available_units": 10
      }
    }

Reglas:

- La referencia es obligatoria y única.
- La categoría debe existir.
- El total de unidades debe ser igual o superior a una.
- Las unidades disponibles se inicializan con el total registrado.
- No se permite registrar una referencia que aparezca en EquipmentReferenceHistory, aunque pertenezca a un implemento dado de baja o que haya cambiado su referencia.
- Al crear el implemento, su referencia se registra permanentemente en EquipmentReferenceHistory dentro de la misma transacción.
- Si la referencia ya fue utilizada, responde HTTP 409 `REFERENCE_ALREADY_EXISTS`.

#### PUT `/equipment/{id}`
Petición:

    {
      "reference": "BAL-001-A",
      "category_id": "uuid",
      "total_units": 12
    }

La actualización permite modificar la referencia, la categoría y el total de unidades.

Reglas:

- La referencia puede modificarse después del registro.
- Si la referencia enviada es igual a la referencia actual, se conserva sin generar un nuevo registro histórico.
- Si la referencia cambia, la nueva referencia no debe existir en EquipmentReferenceHistory.
- No se permite utilizar una referencia que haya sido asignada anteriormente a cualquier implemento, incluso si está dado de baja o si perteneció al mismo implemento.
- Cada nueva referencia se registra permanentemente en EquipmentReferenceHistory.
- El registro histórico y la actualización de Equipment se realizan dentro de una misma transacción.
- Si la referencia ya fue utilizada, responde HTTP 409 `REFERENCE_ALREADY_EXISTS`.
- La categoría debe existir.
- El total de unidades debe ser igual o superior a uno.
- Al modificar `total_units`, se conservan las unidades actualmente prestadas.
- `available_units` se calcula mediante la fórmula: `available_units = new_total_units - loaned_units`.
- No se permite establecer un total inferior a las unidades actualmente prestadas.
- La actualización del total y el recálculo de disponibilidad se realizan transaccionalmente.
- Si el total solicitado produce una disponibilidad negativa, responde HTTP 422 `VALIDATION_ERROR`.

Ejemplo: Si un implemento tiene 10 unidades totales, 3 unidades prestadas y 7 disponibles, al actualizar el total a 12, las unidades disponibles pasan a ser 9.

#### DELETE `/equipment/{id}`
Realiza la baja lógica del implemento mediante `deleted_at`.

Reglas:
- No elimina físicamente el registro.
- Conserva el historial de préstamos.
- No permite utilizar el implemento en nuevos préstamos.
- Las devoluciones pendientes continúan permitidas.
- La referencia actual y todas las referencias históricas del implemento permanecen reservadas permanentemente en EquipmentReferenceHistory.

Respuesta HTTP 204.

### 7.5 Deportistas
| Método | Ruta | Rol | Descripción |
|---|---|---|---|
| GET | `/athletes` | admin, coach | Lista y busca deportistas |
| GET | `/athletes/{id}` | admin, coach | Consulta el detalle de un deportista |
| POST | `/athletes` | admin, coach | Registra un deportista |

#### GET `/athletes`
Parámetros opcionales:
| Parámetro | Descripción |
|---|---|
| `search` | Busca por nombre completo |
| `document_number` | Filtra por número de documento |

Respuesta HTTP 200:

    {
      "data": [
        {
          "id": "uuid",
          "full_name": "Nombre del deportista",
          "document_number": "123456789"
        }
      ]
    }

#### GET `/athletes/{id}`
Consulta un deportista por identificador.

Respuesta HTTP 200:

    {
      "data": {
        "id": "uuid",
        "full_name": "Nombre del deportista",
        "document_number": "123456789"
      }
    }

#### POST `/athletes`
Petición:

    {
      "full_name": "Nombre del deportista",
      "document_number": "123456789"
    }

Respuesta HTTP 201:

    {
      "data": {
        "id": "uuid",
        "full_name": "Nombre del deportista",
        "document_number": "123456789"
      }
    }

Reglas:
- El nombre completo es obligatorio.
- El documento es obligatorio y único.
- Los deportistas no se eliminan físicamente.
- Los registros históricos conservan su relación con el deportista.

### 7.6 Préstamos
| Método | Ruta | Rol | Descripción |
|---|---|---|---|
| POST | `/loans` | coach | Registra un préstamo |
| GET | `/loans` | admin, coach | Lista y consulta préstamos |
| GET | `/loans/{id}` | admin, coach | Consulta un préstamo y sus detalles |
| GET | `/loans/overdue` | admin, coach | Consulta los detalles de préstamos vencidos |

#### POST `/loans`
Petición:

    {
      "athlete_id": "uuid",
      "items": [
        {
          "equipment_id": "uuid",
          "quantity": 2
        },
        {
          "equipment_id": "uuid",
          "quantity": 1
        }
      ]
    }

Respuesta HTTP 201:

    {
      "data": {
        "id": "uuid",
        "athlete_id": "uuid",
        "user_id": "uuid",
        "loaned_at": "2026-11-13T14:00:00Z",
        "items": [
          {
            "id": "uuid",
            "equipment_id": "uuid",
            "equipment_reference": "BAL-001",
            "category_name": "Balones",
            "quantity": 2,
            "due_at": "2026-11-15T14:00:00Z",
            "returned_at": null,
            "status": "pending"
          }
        ]
      }
    }

Reglas:
- El préstamo pertenece a un único deportista.
- El entrenador autenticado queda registrado como responsable.
- El préstamo debe contener al menos un detalle.
- Un mismo implemento no puede repetirse en diferentes detalles del mismo préstamo.
- La cantidad de cada detalle debe ser mayor que cero.
- El total de unidades pendientes del deportista no puede superar tres.
- Un deportista con préstamos vencidos no puede recibir nuevos implementos.
- Los implementos deben estar activos y tener unidades disponibles.
- El plazo de devolución es de 48 horas desde `loaned_at`.
- La referencia y el nombre de la categoría quedan congelados en el detalle del préstamo.
- La creación del préstamo y el descuento de disponibilidad deben realizarse en una única transacción.
- Si una operación falla, no debe registrarse parcialmente el préstamo.
- Si dos entrenadores intentan entregar simultáneamente la última unidad, una operación se confirma y la otra recibe HTTP 409 `EQUIPMENT_NOT_AVAILABLE`.
- Si un implemento está dado de baja, responde HTTP 409 `EQUIPMENT_INACTIVE`.
- Si un implemento activo no tiene unidades suficientes, responde HTTP 409 `EQUIPMENT_NOT_AVAILABLE`.

#### GET `/loans`
Parámetros opcionales:
| Parámetro | Descripción |
|---|---|
| `from` | Fecha inicial del período |
| `to` | Fecha final del período |
| `status` | Estado calculado del préstamo |
| `athlete_id` | Filtra por deportista |
| `user_id` | Filtra por entrenador que registró el préstamo |

Reglas para los filtros de fecha:

- Si no se envían `from` ni `to`, se consulta todo el historial.
- Si se envían ambos parámetros, se consultan los préstamos cuyo `loaned_at` esté dentro del período indicado, incluyendo las fechas límite.
- Si se envía solamente uno de los dos parámetros, responde HTTP 422 `VALIDATION_ERROR`.
- Si `from` es posterior a `to`, responde HTTP 422 `INVALID_DATE_RANGE`.

Estados admitidos:
- `pending`: tiene al menos un detalle pendiente y ninguno vencido.
- `overdue`: tiene al menos un detalle vencido sin devolver.
- `returned`: todos sus detalles han sido devueltos.

Un préstamo que tenga detalles devueltos y otros pendientes conserva el estado `pending`, siempre que ninguno de sus detalles pendientes esté vencido.
Los estados se calculan a partir de las fechas de vencimiento y devolución. No se almacenan como estados independientes.
La fecha inicial no puede ser posterior a la fecha final.

Respuesta HTTP 200:

    {
      "data": [
        {
          "id": "uuid",
          "athlete_id": "uuid",
          "user_id": "uuid",
          "loaned_at": "2026-11-13T14:00:00Z",
          "status": "pending"
        }
      ]
    }

#### GET `/loans/{id}`
Consulta un préstamo con sus detalles y estados calculados.

Respuesta HTTP 200:

    {
      "data": {
        "id": "uuid",
        "athlete_id": "uuid",
        "user_id": "uuid",
        "loaned_at": "2026-11-13T14:00:00Z",
        "status": "pending",
        "items": [
          {
            "id": "uuid",
            "equipment_id": "uuid",
            "equipment_reference": "BAL-001",
            "category_name": "Balones",
            "quantity": 2,
            "due_at": "2026-11-15T14:00:00Z",
            "returned_at": null,
            "status": "pending"
          }
        ]
      }
    }

#### GET `/loans/overdue`
Consulta los detalles de préstamos que están vencidos y todavía no han sido devueltos.

Parámetro opcional:
| Parámetro | Descripción |
|---|---|
| `athlete_id` | Filtra los vencimientos por deportista |

Respuesta HTTP 200:

    {
      "data": [
        {
          "loan_id": "uuid",
          "item_id": "uuid",
          "athlete_id": "uuid",
          "athlete_name": "Nombre del deportista",
          "equipment_reference": "BAL-001",
          "category_name": "Balones",
          "due_at": "2026-11-10T14:00:00Z",
          "days_late": 3
        }
      ]
    }

Un detalle se considera vencido cuando `due_at` es igual o anterior a la fecha y hora actual y `returned_at` permanece en `null`.

### 7.7 Devoluciones
| Método | Ruta | Rol | Descripción |
|---|---|---|---|
| POST | `/loans/{loanId}/items/{itemId}/return` | coach | Registra la devolución completa de un detalle |

Respuesta HTTP 200:

    {
      "data": {
        "id": "uuid",
        "loan_id": "uuid",
        "equipment_id": "uuid",
        "equipment_reference": "BAL-001",
        "quantity": 2,
        "due_at": "2026-11-15T14:00:00Z",
        "returned_at": "2026-11-15T10:00:00Z",
        "status": "returned"
      }
    }

Reglas:
- La devolución se realiza por detalle del préstamo.
- Se devuelven todas las unidades registradas en el detalle.
- El detalle debe estar pendiente.
- No se permite devolver dos veces el mismo detalle.
- La disponibilidad se incrementa por la cantidad completa del detalle.
- La fecha de devolución se registra en UTC.
- La devolución y la actualización de disponibilidad se realizan en una única transacción.
- Las devoluciones de implementos dados de baja siguen permitidas.
- Los registros históricos no se eliminan.

### 7.8 Reportes
| Método | Ruta | Rol | Descripción |
|---|---|---|---|
| GET | `/reports/most-requested` | admin | Consulta los implementos más solicitados |

#### GET `/reports/most-requested`
Parámetros opcionales:
| Parámetro | Descripción |
|---|---|
| `from` | Fecha inicial del período |
| `to` | Fecha final del período |

Comportamiento:
- Si no se envían fechas, se consulta el historial completo.
- Si se desea consultar un período, deben enviarse `from` y `to` conjuntamente.
- Las fechas del período son inclusivas.
- Si solamente se envía una de las dos fechas, responde HTTP 422 `VALIDATION_ERROR`.
- Si la fecha inicial es posterior a la fecha final, responde HTTP 422 `INVALID_DATE_RANGE`.
- Si no existen resultados, devuelve una colección vacía.

El cálculo utiliza la cantidad original registrada en cada detalle del préstamo.
Se incluyen los implementos dados de baja y se conservan los datos históricos congelados.

Respuesta HTTP 200:

    {
      "data": [
        {
          "equipment_reference": "BAL-001",
          "category_name": "Balones",
          "total_requested": 15
        }
      ]
    }

El reporte se ordena de mayor a menor cantidad solicitada.
El reporte no se desglosa por deportista ni por entrenador.

## 8. Seguridad y autorización

### 8.1 Autenticación
El sistema utiliza tokens JWT firmados.

| Característica | Definición |
|---|---|
| Tipo de token | JWT |
| Algoritmo | HS256 |
| Vigencia | 8 horas |
| Identificador del usuario | `sub` |
| Rol | `role` |
| Datos personales | No se incluyen |

Las rutas protegidas requieren:

`Authorization: Bearer <token>`

### 8.2 Contraseñas
- Las contraseñas se almacenan mediante Argon2id utilizando el mecanismo de hashing de Laravel.
- Nunca se almacenan en texto plano.
- No se devuelven en respuestas.
- No deben aparecer en registros de aplicación.

### 8.3 Autorización por rol
| Operación | admin | coach |
|---|---|---|
| Iniciar sesión | Sí | Sí |
| Consultar entrenadores | Sí | No |
| Registrar entrenadores | Sí | No |
| Consultar categorías | Sí | Sí |
| Consultar implementos | Sí | Sí |
| Registrar implementos | Sí | No |
| Actualizar implementos | Sí | No |
| Dar de baja implementos | Sí | No |
| Consultar deportistas | Sí | Sí |
| Registrar deportistas | Sí | Sí |
| Registrar préstamos | No | Sí |
| Consultar préstamos | Sí | Sí |
| Registrar devoluciones | No | Sí |
| Consultar vencidos | Sí | Sí |
| Consultar reporte de más solicitados | Sí | No |

El administrador inicial se provisiona mediante variables de entorno durante la inicialización o despliegue.
No existe registro público de administradores.

### 8.4 CORS
El backend permite únicamente el origen del frontend configurado mediante variables de entorno.

## 9. Consideraciones de consistencia
- Las operaciones de préstamo y devolución deben ejecutarse mediante transacciones.
- La disponibilidad debe permanecer entre cero y el total de unidades.
- Las validaciones de disponibilidad deben protegerse frente a operaciones concurrentes.
- La API debe responder HTTP 409 `EQUIPMENT_NOT_AVAILABLE` cuando exista un conflicto por la última unidad disponible.
- Los estados de préstamos y detalles se calculan, no se almacenan.
- Las operaciones históricas no se eliminan físicamente.
- Las fechas se gestionan en UTC.
- Los mensajes destinados al usuario se presentan en español.
- Los identificadores, rutas, campos y códigos de error se mantienen en inglés.