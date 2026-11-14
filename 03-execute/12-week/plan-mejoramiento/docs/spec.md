# Especificación funcional — Sports Gear

## 1. Información general

| Campo | Descripción |
|---|---|
| Nombre del proyecto | Sports Gear |
| Identificador del dominio | sports-gear |
| Tipo de sistema | Aplicación web para gestionar préstamos de implementos deportivos |
| Contexto | Coliseo |
| Frontend | React |
| Backend | PHP con Laravel |
| Base de datos | Relacional |
| Infraestructura | Docker Compose |

## 2. Propósito

Sports Gear es un sistema web para administrar el catálogo de implementos deportivos de un coliseo y controlar su préstamo y devolución a los deportistas.

El sistema permite consultar la disponibilidad de los implementos, registrar préstamos, controlar las fechas de devolución, identificar préstamos vencidos y consultar cuáles son los implementos más solicitados.

El sistema debe garantizar que las unidades disponibles sean consistentes y evitar que dos operadores entreguen simultáneamente la última unidad disponible.

## 3. Alcance

### 3.1. Funcionalidades incluidas

El sistema contempla:

- Autenticación de usuarios.
- Administración de entrenadores.
- Administración del catálogo de implementos deportivos.
- Consulta y búsqueda de implementos.
- Administración de deportistas.
- Registro de préstamos.
- Registro de devoluciones por implemento.
- Consulta de préstamos registrados.
- Consulta de préstamos vencidos.
- Reporte de implementos más solicitados.
- Control de disponibilidad de unidades.
- Control de las reglas de préstamo.
- Registro histórico de préstamos y devoluciones.
- Sitio público estático para presentar el producto.
- Infraestructura mediante contenedores.
- Herramienta para sembrar datos de prueba mediante la API.

### 3.2. Funcionalidades excluidas

No forman parte del alcance:

- Cobro de multas o pagos.
- Reservas de implementos.
- Renovaciones de préstamos.
- Notificaciones por correo electrónico o SMS.
- Administración de varias sedes o sucursales.
- Importación masiva de información.
- Funcionalidades adicionales no establecidas en esta especificación.

## 4. Actores del sistema

### 4.1. Administrador (Admin)

Es el usuario encargado de la administración general del sistema.

Responsabilidades:

- Gestionar el catálogo de implementos deportivos.
- Registrar y consultar deportistas.
- Registrar entrenadores.
- Consultar la información de los préstamos.
- Consultar los préstamos vencidos.
- Consultar el reporte de implementos más solicitados.

### 4.2. Entrenador (Coach)

Es el usuario encargado de realizar las operaciones de préstamo y devolución.

Responsabilidades:

- Consultar el catálogo de implementos.
- Consultar la disponibilidad de unidades.
- Registrar y consultar deportistas.
- Registrar préstamos.
- Registrar devoluciones.
- Consultar préstamos registrados.
- Consultar préstamos vencidos.

### 4.3. Deportista (Athlete)

Es la persona que solicita implementos deportivos.

El deportista es un registro del sistema y no tiene acceso mediante autenticación.

Información gestionada:

- Nombre completo.
- Número de documento.

## 5. Categorías de implementos

El sistema debe contar con cinco categorías iniciales:

| Identificador | Categoría |
|---|---|
| CAT-01 | Balones |
| CAT-02 | Raquetas |
| CAT-03 | Protección |
| CAT-04 | Atletismo |
| CAT-05 | Gimnasio |

Las categorías son fijas y deben estar disponibles desde la inicialización del sistema.

No se contempla su creación, modificación o eliminación desde la interfaz de administración.

## 6. Historias de usuario

### HU-01. Iniciar sesión

**Como** usuario administrador o entrenador,

**quiero** iniciar sesión con mis credenciales,

**para** acceder a las funcionalidades autorizadas según mi rol.

Criterios de aceptación:

- El sistema permite ingresar las credenciales del usuario.
- El sistema valida las credenciales.
- Si son correctas, permite el acceso.
- Si son incorrectas, muestra un mensaje de error en español.
- El usuario solo puede acceder a las funcionalidades correspondientes a su rol.
- Las contraseñas no se almacenan ni se transmiten en texto plano.

### HU-02. Gestionar entrenadores

**Como** administrador,

**quiero** registrar y consultar entrenadores,

**para** permitir que el personal autorizado opere el sistema.

Criterios de aceptación:

- El administrador puede registrar un entrenador.
- El sistema valida los datos obligatorios.
- El nombre de usuario debe ser único.
- El sistema permite consultar los entrenadores registrados.
- Un entrenador no puede registrar otros usuarios con rol de administrador.
- La información de autenticación debe almacenarse de forma segura.

### HU-03. Gestionar implementos deportivos

**Como** administrador,

**quiero** registrar, consultar, actualizar y dar de baja implementos deportivos,

**para** mantener actualizado el catálogo del coliseo.

Criterios de aceptación:

- El administrador puede registrar un implemento.
- Cada implemento debe tener una referencia que permita identificarlo.
- El implemento debe pertenecer a una de las cinco categorías disponibles.
- El sistema controla el total de unidades y las unidades disponibles.
- El administrador puede consultar y actualizar la información del implemento.
- El sistema permite realizar una baja lógica.
- La referencia continúa siendo única y no puede reutilizarse después de una baja lógica.
- No se permite eliminar físicamente un implemento que tenga operaciones históricas asociadas.
- No se permite asignar una cantidad disponible inferior a cero ni superior al total de unidades.
- Al actualizar el total de unidades, el sistema conserva las unidades prestadas y recalcula las disponibles.
- No se permite reducir el total de unidades por debajo de la cantidad actualmente prestada.

### HU-04. Buscar y consultar implementos

**Como** administrador o entrenador,

**quiero** buscar y consultar implementos deportivos,

**para** conocer sus características y disponibilidad antes de registrar un préstamo.

Criterios de aceptación:

- Los usuarios autenticados pueden consultar el catálogo.
- El sistema permite consultar un implemento individual.
- El sistema permite buscar implementos por su información de identificación.
- Se muestra la categoría correspondiente.
- Se muestran el total de unidades y las unidades disponibles.
- El administrador puede consultar implementos activos, inactivos o todos.
- El entrenador solamente puede consultar implementos activos.
- Los implementos dados de baja no aparecen como disponibles para nuevos préstamos.
- Un implemento inactivo no puede utilizarse en nuevos préstamos.

### HU-05. Gestionar deportistas

**Como** administrador o entrenador,

**quiero** registrar y consultar deportistas,

**para** identificar a las personas que solicitan implementos deportivos.

Criterios de aceptación:

- El sistema permite registrar un deportista.
- El registro contiene nombre completo y número de documento.
- El número de documento debe ser único.
- El sistema permite consultar los deportistas registrados.
- El sistema permite consultar la información de un deportista.
- Los deportistas no necesitan credenciales para utilizar el sistema.

### HU-06. Registrar préstamos

**Como** entrenador,

**quiero** registrar el préstamo de uno o varios implementos a un deportista,

**para** controlar las unidades entregadas y su fecha de devolución.

Criterios de aceptación:

- El entrenador selecciona un deportista registrado.
- El préstamo puede incluir uno o varios implementos, siempre que el total de implementos pendientes de devolución del deportista no supere tres.
- El sistema verifica que cada implemento tenga unidades disponibles.
- El sistema verifica que el deportista no tenga tres o más implementos pendientes de devolución.
- El sistema verifica que el deportista no tenga préstamos vencidos.
- La fecha de vencimiento se calcula a partir de la fecha del préstamo y el plazo de dos días.
- El sistema registra qué entrenador realizó la operación.
- Las unidades disponibles se actualizan al confirmar el préstamo.
- La operación debe ser transaccional: si falla alguna parte, no debe quedar un préstamo parcialmente registrado.
- Si dos entrenadores intentan entregar simultáneamente la última unidad, solamente una operación puede completarse.

### HU-07. Registrar devoluciones

**Como** entrenador,

**quiero** registrar la devolución de los implementos prestados,

**para** actualizar su disponibilidad y conservar el historial de operaciones.

Criterios de aceptación:

- El entrenador puede consultar los implementos pendientes de devolución.
- La devolución se registra por cada implemento del préstamo.
- El sistema registra la fecha de devolución.
- Las unidades disponibles se incrementan al confirmar la devolución.
- No se permite devolver dos veces el mismo implemento dentro del mismo préstamo.
- La operación conserva el historial del préstamo.
- La devolución de un implemento no elimina ni modifica los datos históricos congelados de la operación.

### HU-08. Consultar préstamos

**Como** administrador o entrenador,

**quiero** consultar los préstamos registrados,

**para** conocer los implementos entregados, los deportistas responsables y el estado de las devoluciones.

Criterios de aceptación:

- El sistema permite consultar los préstamos registrados.
- El sistema permite consultar el detalle de un préstamo.
- Se identifica al deportista que recibió los implementos.
- Se identifica al entrenador que registró la operación.
- Se muestran las fechas del préstamo y de vencimiento.
- Se identifica qué implementos han sido devueltos y cuáles siguen pendientes.
- El estado del préstamo se obtiene a partir de sus fechas y devoluciones.

### HU-09. Consultar préstamos vencidos

**Como** administrador o entrenador,

**quiero** consultar los préstamos vencidos,

**para** identificar los implementos que no fueron devueltos dentro del plazo establecido.

Criterios de aceptación:

- El sistema permite consultar los implementos cuyo plazo de devolución ha vencido y siguen pendientes.
- Se muestra la información del deportista responsable.
- Se muestra la información del implemento.
- Se muestran la fecha del préstamo y la fecha de vencimiento.
- El estado de vencido se calcula a partir de las fechas y la devolución.
- Un deportista con préstamos vencidos no puede recibir nuevos implementos hasta devolver los pendientes vencidos.

### HU-10. Consultar implementos más solicitados

**Como** administrador,

**quiero** consultar un reporte de los implementos más solicitados,

**para** conocer cuáles tienen mayor demanda en el coliseo.

Criterios de aceptación:

- El sistema genera un reporte a partir de los préstamos registrados.
- El reporte identifica los implementos con mayor cantidad de solicitudes.
- La información se obtiene de las operaciones registradas.
- El reporte no modifica el catálogo ni el historial de préstamos.
- La consulta está disponible para el administrador.

## 7. Reglas de negocio

### RN-01. Control de unidades disponibles

Las unidades disponibles de un implemento deben permanecer siempre entre cero y el total de unidades registradas.

La condición es:

`0 <= available_units <= total_units`

El sistema debe impedir operaciones que dejen cantidades fuera de ese intervalo.

### RN-02. Límite y plazo de préstamos

Cada deportista puede tener como máximo tres implementos pendientes de devolución.

El plazo de devolución de cada implemento prestado es de dos días, calculados a partir de la fecha del préstamo.

### RN-03. Bloqueo por préstamos vencidos

Un deportista que tenga implementos con préstamos vencidos pendientes de devolución no puede recibir nuevos implementos.

El bloqueo se mantiene hasta que se registren las devoluciones correspondientes.

### RN-04. Conservación de información histórica

Los datos del implemento asociados a un préstamo deben conservarse como una copia histórica.

Los cambios posteriores realizados en el catálogo no deben modificar la información histórica de los préstamos existentes.

### RN-05. Conservación de operaciones y baja lógica

Los préstamos y las devoluciones no se pueden eliminar.

Los implementos pueden darse de baja mediante eliminación lógica, conservando su información y las operaciones históricas relacionadas.

### RN-06. Fechas y lenguaje

Las fechas y horas utilizadas para almacenar y procesar operaciones deben manejarse en UTC.

Los mensajes visibles para el usuario deben estar en español.

Los identificadores técnicos del código y de la base de datos deben estar en inglés.

## 8. Requisitos no funcionales

### RNF-01. Rendimiento de búsqueda

Las consultas de búsqueda del catálogo deben responder en menos de 300 milisegundos bajo una carga de referencia de 5.000 implementos.

### RNF-02. Concurrencia en la última unidad

Cuando dos operadores intenten prestar simultáneamente la última unidad disponible de un implemento:

- Una operación debe completarse correctamente.
- La otra operación debe rechazarse.
- La operación rechazada debe recibir una respuesta HTTP 409 Conflict.
- La disponibilidad no puede quedar en un valor negativo.

### RNF-03. Seguridad de credenciales

Las contraseñas no deben almacenarse ni transmitirse en texto plano.

Las contraseñas no deben aparecer en registros de aplicación ni en mensajes de error.

### RNF-04. Uniformidad de errores HTTP

Las respuestas de error de la API deben utilizar una estructura uniforme.

Los mensajes dirigidos al usuario deben estar en español.

La estructura debe permitir identificar el código del error, el mensaje y los detalles correspondientes.

### RNF-05. Despliegue reproducible

El sistema debe poder iniciarse mediante un único comando de Docker Compose.

La infraestructura debe permitir iniciar la aplicación con una base de datos vacía, aplicando las migraciones y los procesos de inicialización necesarios.

## 9. Restricciones técnicas y de desarrollo

- El frontend debe desarrollarse con React.
- El backend debe desarrollarse con PHP y Laravel.
- La persistencia debe utilizar una base de datos relacional.
- La infraestructura debe gestionarse mediante Docker Compose.
- La herramienta de sembrado debe utilizar la API del sistema.
- Los identificadores de clases, métodos, funciones, variables, propiedades y columnas deben estar en inglés.
- Los textos visibles para los usuarios deben estar en español.
- Las funcionalidades implementadas deben corresponder al alcance de esta especificación.
- La documentación técnica debe mantenerse alineada con el comportamiento real del sistema.

## 10. Criterios generales de aceptación

El producto se considera funcionalmente aceptable cuando:

1. Las diez historias de usuario están implementadas.
2. Las seis reglas de negocio se cumplen.
3. Los cinco requisitos no funcionales cuentan con evidencia de verificación.
4. Las cinco categorías iniciales están disponibles.
5. Los préstamos y devoluciones conservan su historial.
6. La disponibilidad de unidades se mantiene consistente.
7. La concurrencia sobre la última unidad se controla correctamente.
8. Los roles de administrador y entrenador tienen acceso según sus responsabilidades.
9. El reporte de implementos más solicitados utiliza los datos de los préstamos registrados.
10. La documentación y el comportamiento implementado mantienen congruencia.