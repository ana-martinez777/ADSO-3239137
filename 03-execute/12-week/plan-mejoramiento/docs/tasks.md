# Tareas de desarrollo — Sports Gear

Este documento organiza las tareas necesarias para construir Sports Gear, de acuerdo con la constitución, la especificación funcional, el plan de desarrollo, la arquitectura, el modelo de datos y el contrato API.

Cada tarea debe contar con evidencia verificable para considerarse terminada.

Estado inicial de todas las tareas: pendiente.

## 1. Plan de tareas

| # | Tarea | Depende de | Evidencia |
|---|---|---|---|
| T-01 | Estructura inicial del proyecto Laravel y endpoint `GET /api/v1/health` | — | La API responde `{"status":"ok"}` y el proyecto inicia correctamente. |
| T-02 | Migraciones iniciales: siete tablas, claves primarias, claves foráneas, restricciones, índices y las cinco categorías iniciales | T-01 | Las tablas y restricciones coinciden con `data-model.md`; las cinco categorías están disponibles después de ejecutar el sembrado inicial. |
| T-03 | Dominio: objetos de valor y validaciones de cantidad y rango de fechas | T-01 | Pruebas unitarias verifican cantidades mayores que cero y rangos de fechas válidos. |
| T-04 | Dominio: `Equipment`, disponibilidad, retiro de unidades, reposición, actualización de unidades y baja lógica | T-03 | Pruebas verifican que las unidades disponibles nunca sean negativas ni superen el total, y que la actualización del total conserve las unidades prestadas. |
| T-05 | Dominio: `Loan` y `LoanItem`, registro de detalles, devolución completa y estados calculados | T-04 | Pruebas verifican el plazo de 48 horas, el máximo de tres unidades pendientes, los estados `pending`, `overdue` y `returned`, y la devolución única por detalle. |
| T-06 | Dominio: `Athlete`, `User` y `Category`, identificadores y reglas de cada entidad | T-03 | Pruebas verifican la unicidad del documento del deportista, la normalización del nombre de usuario y los roles permitidos. |
| T-07 | Puertos y casos de uso de autenticación y administración de entrenadores | T-06 | Los casos de uso de inicio de sesión y registro de entrenadores cumplen HU-01 y HU-02 mediante pruebas con repositorios simulados. |
| T-08 | Puertos y casos de uso de implementos deportivos: registrar, consultar, buscar, actualizar y dar de baja | T-04, T-06 | Los casos de uso cumplen HU-03 y HU-04, incluyendo disponibilidad, reserva permanente de referencias históricas, cambios de referencia y conservación del historial. |
| T-09 | Caso de uso para registrar préstamos de implementos | T-05, T-08 | HU-06 cumple las reglas de máximo tres unidades pendientes, préstamos vencidos, disponibilidad, plazo de 48 horas y registro transaccional. |
| T-10 | Casos de uso para registrar devoluciones y consultar préstamos vencidos | T-09 | HU-07 y HU-09 cumplen la devolución completa por detalle, la actualización de disponibilidad y la prevención de devoluciones duplicadas. |
| T-11 | Caso de uso para generar el reporte de implementos más solicitados | T-09 | HU-10 calcula las cantidades solicitadas utilizando los registros históricos y permite consultar el historial completo o un período determinado. |
| T-12 | Adaptadores de persistencia y repositorios de implementos, historial de referencias, deportistas, usuarios y categorías | T-02, T-08 | Pruebas de integración verifican las operaciones de persistencia y la reserva permanente de referencias contra MySQL 8.4. |
| T-13 | Adaptador de persistencia de préstamos, detalles y control de concurrencia | T-12, T-09, T-10 | Dos operaciones concurrentes sobre la última unidad producen una operación exitosa y otra un conflicto de disponibilidad que el adaptador HTTP traduce a HTTP 409 `EQUIPMENT_NOT_AVAILABLE`. |
| T-14 | Adaptador de seguridad: hashing de contraseñas y autenticación mediante JWT | T-07 | Las contraseñas se almacenan mediante Argon2id y nunca aparecen en texto plano en respuestas ni registros de aplicación. |
| T-15 | Adaptación del manejo de fechas y hora del sistema | T-03, T-05 | Las operaciones utilizan fechas UTC; las fechas de vencimiento y devolución se calculan correctamente y pueden probarse con un reloj controlado. |
| T-16 | Adaptador HTTP: implementación de rutas del `api-contract.md`, validaciones, respuestas y autorización por rol | T-10, T-11, T-13, T-14, T-15 | Las pruebas de contrato verifican los endpoints, los códigos HTTP, el cuerpo uniforme de errores y los permisos de administrador y entrenador. |
| T-17 | Composición de dependencias e inicialización del sistema | T-16 | El sistema inicia con una base de datos vacía, ejecuta sus migraciones y permite iniciar sesión con el administrador inicial configurado mediante variables de entorno. |
| T-18 | Verificación de invariantes y restricciones de integridad en MySQL | T-12 | Las restricciones definidas en `data-model.md` impiden cantidades inválidas, disponibilidades fuera de rango y referencias históricas duplicadas. Las reglas que corresponden al dominio se verifican mediante pruebas automatizadas. |
| T-19 | Pruebas automatizadas de arquitectura según los artículos I, II y III de `constitution.md` | T-17 | Las pruebas verifican la independencia del dominio, la dirección de las dependencias y el punto único de composición. |
| T-20 | Infraestructura con Docker Compose para MySQL, API Laravel y frontend React | T-17 | Un solo comando permite levantar los contenedores y comprobar la comunicación entre los servicios. |
| T-21 | Herramienta de sembrado de datos de demostración mediante la API | T-17 | El sembrador registra al menos 40 implementos, 10 deportistas y 15 préstamos, incluyendo al menos 3 préstamos vencidos. Las consultas de vencidos y el reporte devuelven resultados. |
| T-22 | Aplicación frontend en React: autenticación, catálogo, deportistas, préstamos, devoluciones, vencidos y reportes | T-16 | Se realiza un recorrido funcional de los procesos definidos en las historias de usuario y se respetan los permisos de cada rol. |
| T-23 | Sitio público estático de presentación de Sports Gear | — | El sitio presenta el producto, funciona sin depender de la API y se visualiza correctamente en dispositivos móviles y de escritorio. |

## 2. Criterios generales de finalización

Una tarea se considera terminada cuando:

- Su implementación cumple lo establecido en los documentos de especificación.
- Cuenta con evidencia verificable.
- Las pruebas correspondientes se ejecutan correctamente.
- No introduce funcionalidades fuera del alcance definido.
- Respeta la arquitectura y las convenciones de `constitution.md`.
- No contiene credenciales, archivos `.env` ni dependencias instaladas dentro del repositorio.

## 3. Cobertura de pruebas

El proyecto debe contar con pruebas automatizadas que verifiquen:

- Reglas y comportamientos del dominio.
- Casos de uso.
- Persistencia y restricciones de MySQL.
- Concurrencia en el préstamo de unidades.
- Autenticación y autorización.
- Endpoints y respuestas del contrato API.
- Reglas de arquitectura.

La entrega debe incluir al menos 15 archivos de pruebas automatizadas.

## 4. Organización del trabajo

- Las tareas se desarrollan respetando sus dependencias.
- Cada tarea debe contar con evidencia antes de marcarse como terminada.
- Los cambios se registran mediante commits pequeños y descriptivos.
- Los commits deben distribuirse en al menos ocho días distintos.
- No se debe reescribir el historial de Git.
- No se deben incluir archivos `.env` ni carpetas `node_modules`.

## 5. Relación con los documentos del proyecto

Este documento debe mantenerse alineado con:

- `constitution.md`
- `spec.md`
- `plan.md`
- `architecture.md`
- `data-model.md`
- `api-contract.md`
- Documentos de decisiones arquitectónicas ubicados en `adr/`