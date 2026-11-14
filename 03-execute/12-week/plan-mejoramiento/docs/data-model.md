# Data Model — Sports Gear

## 1. Propósito

Este documento define el modelo de datos de Sports Gear, incluyendo las entidades, sus atributos, relaciones, restricciones e invariantes de persistencia.

El modelo representa el préstamo de implementos deportivos de un coliseo y conserva la trazabilidad de los préstamos y las devoluciones.

## 2. Decisiones técnicas

| Decisión | Definición |
|---|---|
| Motor de base de datos | MySQL |
| Versión | MySQL 8.4 |
| Motor de almacenamiento | InnoDB |
| Identificadores | UUID v4 |
| Almacenamiento de UUID | CHAR(36) |
| Codificación | utf8mb4 |
| Zona horaria | UTC |
| Tipo de fecha y hora | DATETIME(6) |
| Integridad referencial | ON DELETE RESTRICT |
| Eliminación de catálogo | Baja lógica mediante deleted_at, únicamente para Equipment |

Los identificadores UUID se almacenan como cadenas de 36 caracteres, incluyendo los guiones.

Todas las fechas y horas se gestionan en UTC. Las conexiones de la aplicación con MySQL deben utilizar UTC para evitar conversiones de zona horaria.

La aplicación utiliza DATETIME(6) para conservar precisión de microsegundos.

Las fechas enviadas y recibidas mediante la API utilizan formato ISO 8601 en UTC, con indicador Z.

Los identificadores de tablas y columnas se mantienen en inglés.

No se contemplan columnas de auditoría created_at y updated_at, porque no forman parte de los requisitos definidos.

## 3. Entidades

El modelo está compuesto por seis entidades:

1. User
2. Athlete
3. Category
4. Equipment
5. Loan
6. LoanItem

### 3.1 User

Representa a los usuarios autorizados para acceder al sistema.

| Atributo | Tipo lógico | Tipo físico | Restricciones | Descripción |
|---|---|---|---|---|
| id | UUID | CHAR(36) | PK | Identificador del usuario |
| username | String | VARCHAR(120) | NOT NULL, UNIQUE | Nombre de usuario |
| password_hash | String | VARCHAR(255) | NOT NULL | Contraseña almacenada mediante hash seguro |
| role | String | VARCHAR(20) | NOT NULL | Rol: admin o coach |

Reglas:

- El nombre de usuario debe ser único.
- El nombre de usuario se normaliza eliminando espacios externos y convirtiéndolo a minúsculas.
- La contraseña nunca se almacena en texto plano.
- El sistema contempla los roles admin y coach.
- El administrador inicial se provisiona mediante variables de entorno durante la inicialización o despliegue.
- No existe registro público de administradores.
- Las contraseñas se procesan mediante Argon2id utilizando el mecanismo de hashing de Laravel.
- Las contraseñas y sus hashes nunca se devuelven en las respuestas de la API.
- Las credenciales y los hashes no deben aparecer en los registros de aplicación.
- Los usuarios permanecen registrados para conservar la autoría de las operaciones históricas.
- No se contempla baja lógica, desactivación ni eliminación física de usuarios mediante las funcionalidades de la aplicación.
- Las claves foráneas impiden eliminar físicamente usuarios que tengan préstamos asociados.

### 3.2 Athlete

Representa a los deportistas que reciben implementos deportivos en préstamo.

| Atributo | Tipo lógico | Tipo físico | Restricciones | Descripción |
|---|---|---|---|---|
| id | UUID | CHAR(36) | PK | Identificador del deportista |
| full_name | String | VARCHAR(200) | NOT NULL | Nombre completo |
| document_number | String | VARCHAR(30) | NOT NULL, UNIQUE | Número de documento |

Reglas:

- El nombre completo y el número de documento son obligatorios.
- El número de documento debe ser único.
- Los deportistas permanecen registrados para conservar sus relaciones con los préstamos históricos.
- No se contempla baja lógica, desactivación ni eliminación física de deportistas mediante las funcionalidades de la aplicación.
- Las claves foráneas impiden eliminar físicamente deportistas que tengan préstamos asociados.

### 3.3 Category

Representa las categorías fijas utilizadas para clasificar los implementos deportivos.

| Atributo | Tipo lógico | Tipo físico | Restricciones | Descripción |
|---|---|---|---|---|
| id | UUID | CHAR(36) | PK | Identificador de la categoría |
| name | String | VARCHAR(120) | NOT NULL, UNIQUE | Nombre de la categoría |

Categorías iniciales:

1. Balones
2. Raquetas
3. Protección
4. Atletismo
5. Gimnasio

Reglas:

- Las categorías se crean mediante el sembrador inicial de datos.
- Los nombres de las categorías deben ser únicos.
- No se permite crear, modificar ni eliminar categorías desde la aplicación.
- Una categoría relacionada con implementos no puede eliminarse físicamente.

### 3.4 Equipment

Representa los tipos o modelos de implementos deportivos disponibles en el inventario.

La referencia identifica el tipo de implemento, no cada unidad física individual.

| Atributo | Tipo lógico | Tipo físico | Restricciones | Descripción |
|---|---|---|---|---|
| id | UUID | CHAR(36) | PK | Identificador del implemento |
| reference | String | VARCHAR(120) | NOT NULL, UNIQUE | Referencia del implemento |
| category_id | UUID | CHAR(36) | FK, NOT NULL | Categoría a la que pertenece |
| total_units | Integer | INT | NOT NULL, >= 1 | Cantidad total de unidades |
| available_units | Integer | INT | NOT NULL, >= 0 | Cantidad de unidades disponibles |
| deleted_at | DateTime UTC | DATETIME(6) | NULL | Fecha de baja lógica |

Relaciones:

- Cada implemento pertenece a una categoría.
- Una categoría puede tener varios implementos.

Reglas:

- La referencia es única globalmente.
- Una referencia no puede reutilizarse después de dar de baja un implemento.
- La cantidad total debe ser igual o superior a una unidad.
- Las unidades disponibles nunca pueden ser negativas.
- Las unidades disponibles no pueden superar el total registrado.
- Un implemento dado de baja no puede utilizarse en nuevos préstamos.
- Las devoluciones de préstamos anteriores siguen permitidas aunque el implemento esté dado de baja.
- Un implemento puede reactivarse conservando su identificador y su historial.
- Los implementos se eliminan mediante baja lógica.
- La referencia permanece reservada incluso después de la baja lógica.
- Los registros relacionados con préstamos no se eliminan físicamente.

La columna deleted_at tiene el siguiente comportamiento:

- NULL: implemento activo.
- Fecha registrada: implemento dado de baja.

### 3.5 Loan

Representa la operación de préstamo realizada a un deportista.

| Atributo | Tipo lógico | Tipo físico | Restricciones | Descripción |
|---|---|---|---|---|
| id | UUID | CHAR(36) | PK | Identificador del préstamo |
| athlete_id | UUID | CHAR(36) | FK, NOT NULL | Deportista que recibe los implementos |
| user_id | UUID | CHAR(36) | FK, NOT NULL | Usuario que registra el préstamo |
| loaned_at | DateTime UTC | DATETIME(6) | NOT NULL | Fecha y hora del préstamo |

Relaciones:

- Cada préstamo pertenece a un deportista.
- Cada préstamo es registrado por un usuario.
- Un préstamo contiene uno o varios detalles de préstamo.

Reglas:

- Un préstamo debe tener al menos un detalle.
- El usuario que registra la operación debe estar autorizado.
- Solamente el rol coach registra préstamos.
- La operación conserva la identidad del usuario que la registró.
- Los préstamos son registros históricos e inmutables.
- Los préstamos no se eliminan físicamente.
- La fecha de vencimiento se calcula en cada detalle del préstamo.
- La fecha y hora del préstamo se registra en UTC.

### 3.6 LoanItem

Representa el detalle de un préstamo y las unidades de una referencia de implemento entregadas al deportista.

Se utiliza una cantidad agrupada para registrar varias unidades de una misma referencia dentro de una sola línea.

| Atributo | Tipo lógico | Tipo físico | Restricciones | Descripción |
|---|---|---|---|---|
| id | UUID | CHAR(36) | PK | Identificador del detalle |
| loan_id | UUID | CHAR(36) | FK, NOT NULL | Préstamo al que pertenece |
| equipment_id | UUID | CHAR(36) | FK, NOT NULL | Implemento entregado |
| equipment_reference | String | VARCHAR(120) | NOT NULL | Referencia histórica del implemento |
| category_name | String | VARCHAR(120) | NOT NULL | Categoría histórica del implemento |
| quantity | Integer | INT | NOT NULL, > 0 | Cantidad de unidades entregadas |
| due_at | DateTime UTC | DATETIME(6) | NOT NULL | Fecha y hora límite de devolución |
| returned_at | DateTime UTC | DATETIME(6) | NULL | Fecha y hora de devolución completa |

Relaciones:

- Cada detalle pertenece a un préstamo.
- Cada detalle referencia un implemento.

Restricciones:

- La combinación loan_id y equipment_id debe ser única.
- Un mismo implemento no puede repetirse en varias líneas del mismo préstamo.
- La cantidad debe ser mayor que cero.
- La fecha de vencimiento se calcula sumando 48 horas a la fecha y hora del préstamo.
- La información equipment_reference y category_name se conserva como copia histórica.
- Los cambios posteriores en el catálogo no modifican los datos históricos del préstamo.
- Un detalle solo puede devolverse una vez.
- La devolución debe ser completa: se devuelven todas las unidades registradas en el detalle.
- returned_at permanece NULL mientras el detalle esté pendiente de devolución.
- Al confirmar la devolución, returned_at registra la fecha y hora UTC.
- Solamente el rol coach registra devoluciones.
- Los detalles de préstamo no se eliminan físicamente.
- La información histórica permanece inmutable, excepto por la transición de returned_at desde NULL hasta la fecha de devolución.

La fecha de vencimiento se calcula de la siguiente manera:

due_at = loaned_at + 48 horas

#### Estado calculado del detalle

El estado no se almacena como una columna. Se obtiene a partir de la fecha de vencimiento y de returned_at.

| Condición | Estado calculado |
|---|---|
| returned_at es NULL y due_at es posterior a la fecha y hora actual | Pendiente |
| returned_at es NULL y due_at es igual o anterior a la fecha y hora actual | Vencido |
| returned_at tiene una fecha registrada | Devuelto |

Un detalle se considera vencido cuando:

- La fecha y hora actual es igual o posterior a due_at.
- returned_at permanece en NULL.

## 4. Relaciones entre entidades

| Entidad origen | Entidad destino | Cardinalidad | Descripción |
|---|---|---|---|
| Category | Equipment | 1:N | Una categoría contiene varios implementos |
| Athlete | Loan | 1:N | Un deportista puede tener varios préstamos |
| User | Loan | 1:N | Un usuario puede registrar varios préstamos |
| Loan | LoanItem | 1:N | Un préstamo contiene uno o varios detalles |
| Equipment | LoanItem | 1:N | Un implemento puede aparecer en varios detalles históricos |

## 5. Integridad referencial

Todas las claves foráneas utilizan la política ON DELETE RESTRICT.

| Relación | Política |
|---|---|
| Equipment.category_id → Category.id | RESTRICT |
| Loan.athlete_id → Athlete.id | RESTRICT |
| Loan.user_id → User.id | RESTRICT |
| LoanItem.loan_id → Loan.id | RESTRICT |
| LoanItem.equipment_id → Equipment.id | RESTRICT |

No se utilizan eliminaciones en cascada.

La eliminación física de registros relacionados con operaciones históricas está prohibida.

La baja lógica mediante deleted_at se utiliza exclusivamente para Equipment.

Las claves primarias UUID no se modifican. Las relaciones utilizan ON UPDATE RESTRICT.

## 6. Restricciones e invariantes de negocio

### 6.1 Inventario

- total_units debe ser mayor o igual a uno.
- available_units debe ser mayor o igual a cero.
- available_units no puede superar total_units.
- La entrega de unidades disminuye la disponibilidad.
- La devolución de unidades incrementa la disponibilidad.
- Las operaciones de préstamo y devolución deben mantener la consistencia del inventario.
- Los implementos dados de baja no pueden utilizarse en nuevos préstamos.
- Las devoluciones de implementos dados de baja siguen permitidas.

### 6.2 Préstamos

- Un deportista puede tener como máximo tres unidades pendientes de devolución.
- El límite se calcula considerando todos sus préstamos.
- Un deportista con préstamos vencidos no puede recibir nuevos implementos.
- Cada detalle tiene un plazo de devolución de dos días.
- El plazo corresponde a 48 horas desde loaned_at.
- Los implementos dados de baja no pueden utilizarse en nuevos préstamos.
- Un préstamo debe contener al menos un detalle.
- El entrenador es quien registra los préstamos.

### 6.3 Devoluciones

- La devolución se realiza por cada detalle del préstamo.
- Se deben devolver todas las unidades registradas en el detalle.
- No se permite devolver dos veces el mismo detalle.
- La fecha de devolución se registra en returned_at.
- La disponibilidad se incrementa por la cantidad total del detalle devuelto.
- Los registros históricos de préstamos y devoluciones son inmutables.
- El entrenador es quien registra las devoluciones.

### 6.4 Usuarios y deportistas

- Los usuarios y deportistas permanecen registrados para conservar el historial de operaciones.
- No se contempla baja lógica ni desactivación de usuarios o deportistas.
- Los registros históricos conservan sus relaciones.
- Las claves foráneas impiden eliminar físicamente registros relacionados con operaciones históricas.
- Los usuarios y deportistas no tienen mecanismos de reactivación porque no se contempla su desactivación.

## 7. Concurrencia y transacciones

Las operaciones que modifican la disponibilidad de los implementos deben ejecutarse dentro de transacciones de base de datos.

Los bloqueos se implementan mediante SELECT ... FOR UPDATE utilizando MySQL e InnoDB.

### Para registrar un préstamo

1. Iniciar una transacción.
2. Bloquear el registro del deportista mediante SELECT ... FOR UPDATE.
3. Verificar que el deportista exista.
4. Consultar si el deportista tiene préstamos vencidos.
5. Consultar la cantidad total de unidades pendientes.
6. Verificar que el nuevo préstamo no supere el máximo de tres unidades pendientes.
7. Bloquear los registros de implementos involucrados mediante SELECT ... FOR UPDATE.
8. Ordenar los bloqueos de implementos por identificador para mantener un orden determinista.
9. Verificar que los implementos estén activos.
10. Verificar nuevamente las unidades disponibles.
11. Registrar el préstamo y sus detalles.
12. Descontar las unidades disponibles.
13. Confirmar la transacción.

Si alguna validación falla, la transacción debe revertirse.

El bloqueo del deportista evita que dos operaciones concurrentes superen el límite de tres unidades pendientes o permitan nuevos préstamos cuando existen vencidos.

El bloqueo de los implementos evita que dos operadores entreguen simultáneamente la última unidad disponible.

Si dos operadores intentan entregar la última unidad simultáneamente, solamente una operación debe confirmarse. La otra debe recibir HTTP 409 Conflict.

### Para registrar una devolución

1. Iniciar una transacción.
2. Bloquear el detalle del préstamo mediante SELECT ... FOR UPDATE.
3. Verificar que el detalle todavía esté pendiente.
4. Bloquear el registro del implemento asociado mediante SELECT ... FOR UPDATE.
5. Registrar la fecha de devolución en returned_at.
6. Incrementar las unidades disponibles por la cantidad completa del detalle.
7. Confirmar la transacción.

El bloqueo del detalle evita que dos operaciones registren la misma devolución.

El bloqueo del implemento evita actualizaciones concurrentes que produzcan inconsistencias en las unidades disponibles.

Si ocurre un error durante cualquiera de estas operaciones, la transacción debe revertirse para evitar inconsistencias.

## 8. Índices y restricciones de unicidad

Se deben establecer índices para las claves foráneas, las restricciones de unicidad y las consultas principales.

| Tabla | Índice o restricción | Propósito |
|---|---|---|
| User | PRIMARY KEY(id) | Identificar usuarios |
| User | UNIQUE(username) | Evitar nombres de usuario duplicados |
| User | CHECK(role IN ('admin', 'coach')) | Limitar los roles permitidos |
| Athlete | PRIMARY KEY(id) | Identificar deportistas |
| Athlete | UNIQUE(document_number) | Evitar documentos duplicados |
| Category | PRIMARY KEY(id) | Identificar categorías |
| Category | UNIQUE(name) | Evitar categorías duplicadas |
| Equipment | PRIMARY KEY(id) | Identificar implementos |
| Equipment | UNIQUE(reference) | Evitar referencias duplicadas globalmente |
| Equipment | INDEX(category_id, reference) | Consultar implementos por categoría y referencia |
| Equipment | CHECK(total_units >= 1) | Garantizar al menos una unidad total |
| Equipment | CHECK(available_units >= 0 AND available_units <= total_units) | Mantener la disponibilidad dentro de los límites |
| Loan | PRIMARY KEY(id) | Identificar préstamos |
| Loan | INDEX(athlete_id, loaned_at) | Consultar préstamos de un deportista por fecha |
| Loan | INDEX(user_id) | Consultar préstamos registrados por usuario |
| Loan | INDEX(loaned_at) | Consultar préstamos por período |
| LoanItem | PRIMARY KEY(id) | Identificar detalles |
| LoanItem | UNIQUE(loan_id, equipment_id) | Evitar líneas duplicadas en un préstamo |
| LoanItem | INDEX(equipment_id) | Consultar historial por implemento |
| LoanItem | INDEX(returned_at, due_at) | Consultar detalles pendientes y vencidos |
| LoanItem | CHECK(quantity > 0) | Garantizar cantidades positivas |

Las claves primarias y restricciones UNIQUE generan sus respectivos índices.

Las claves foráneas también requieren índices de soporte. Cuando una clave foránea ya está cubierta por el prefijo izquierdo de otro índice, no debe crearse un índice redundante.

El índice compuesto de LoanItem permite consultar los detalles pendientes mediante returned_at IS NULL y filtrar por due_at.

La consulta de vencimientos debe utilizar las siguientes condiciones:

- returned_at IS NULL.
- due_at <= UTC_TIMESTAMP(6).

MySQL 8.4 no admite índices parciales convencionales como PostgreSQL. Por ello, el índice de vencimientos se define como un índice compuesto normal.

Las reglas que dependen de registros relacionados, como el límite de tres unidades pendientes y la verificación de devoluciones, deben validarse mediante transacciones y bloqueos de registros.

## 9. Reporte de implementos más solicitados

El reporte se obtiene a partir de los detalles de préstamo.

Criterio de cálculo:

- Se suma la cantidad original registrada en LoanItem.quantity.
- No se utiliza la cantidad devuelta para calcular la demanda.
- El período de consulta puede ser histórico completo o un rango de fechas.
- El rango se determina utilizando Loan.loaned_at.
- Se incluyen los implementos dados de baja.
- Se utilizan los datos históricos conservados en LoanItem.
- El reporte está disponible únicamente para el administrador.

El reporte no requiere una tabla adicional, ya que sus datos se obtienen mediante consultas sobre los préstamos y sus detalles.

## 10. Consideraciones de persistencia

- Las migraciones de Laravel serán la fuente de verdad del esquema de la base de datos.
- Las relaciones se implementarán mediante claves foráneas.
- Todas las claves foráneas utilizarán ON DELETE RESTRICT.
- Las operaciones históricas no se eliminan físicamente.
- Las bajas lógicas se representan mediante deleted_at únicamente para Equipment.
- Los estados de los detalles se calculan a partir de las fechas de vencimiento y devolución.
- No se almacenan estados derivados que puedan generar inconsistencias.
- Las fechas y horas se gestionan en UTC.
- Las conexiones de Laravel con MySQL deben utilizar UTC.
- Los bloqueos transaccionales deben respetar el orden definido para evitar inconsistencias y reducir el riesgo de interbloqueos.
- Las restricciones CHECK deben implementarse en las migraciones y validarse también desde la aplicación.
- No se crean tablas adicionales para reportes, multas o auditorías que no estén contempladas en el alcance.

## 11. Resumen del modelo

| Entidad | Responsabilidad principal |
|---|---|
| User | Usuarios autorizados del sistema |
| Athlete | Deportistas registrados |
| Category | Clasificación fija de implementos |
| Equipment | Inventario de implementos deportivos |
| Loan | Operación general de préstamo |
| LoanItem | Detalle, cantidad entregada y devolución del implemento |

Este modelo busca garantizar la integridad del inventario, la trazabilidad de las operaciones y la conservación del historial de préstamos y devoluciones.