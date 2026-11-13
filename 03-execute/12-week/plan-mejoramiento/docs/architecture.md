# Arquitectura del sistema — Sports Gear

## 1. Información general

| Campo | Descripción |
|---|---|
| Proyecto | Sports Gear |
| Dominio | sports-gear |
| Backend | PHP con Laravel |
| Frontend | React |
| Persistencia | MySQL |
| Infraestructura | Docker Compose |
| Estilo arquitectónico | Arquitectura hexagonal |
| Organización | Separación por responsabilidades y dependencias dirigidas hacia el dominio |

## 2. Objetivo de la arquitectura

Definir la organización técnica de Sports Gear, las responsabilidades de sus componentes y las reglas que deben respetarse durante la implementación.

La arquitectura busca mantener separadas las reglas de negocio de los mecanismos externos, como Laravel, HTTP, la base de datos y React.

El diseño debe permitir que las reglas del dominio puedan probarse sin depender directamente del framework o de la infraestructura.

## 3. Estilo arquitectónico

Se utilizará una arquitectura hexagonal, también conocida como arquitectura de puertos y adaptadores.

Este enfoque organiza el sistema alrededor del dominio y establece límites entre:

- El núcleo que contiene las reglas de negocio.
- La aplicación que coordina los casos de uso.
- Los adaptadores que permiten comunicarse con el exterior.

Las dependencias deben dirigirse hacia el núcleo del sistema.

Laravel será utilizado como framework del backend, pero no deberá convertirse en el lugar donde se concentren todas las reglas de negocio.

## 4. Capas y responsabilidades

### 4.1. Dominio

Contiene las reglas y los comportamientos propios de Sports Gear.

Responsabilidades:

- Representar los conceptos principales del negocio.
- Controlar las reglas de disponibilidad.
- Controlar las operaciones permitidas sobre los implementos.
- Aplicar las reglas de préstamo.
- Aplicar las reglas de devolución.
- Calcular los estados derivados de los préstamos.
- Mantener las invariantes del dominio.

El dominio no debe depender de:

- Laravel.
- Eloquent ORM.
- Controladores HTTP.
- React.
- La base de datos.
- Servicios externos.

### 4.2. Aplicación

Contiene los casos de uso que coordinan las operaciones del sistema.

Responsabilidades:

- Ejecutar los casos de uso.
- Coordinar las operaciones del dominio.
- Consultar y almacenar información mediante puertos.
- Aplicar las validaciones correspondientes al flujo de cada operación.
- Controlar las transacciones mediante las abstracciones definidas.
- Gestionar los resultados de las operaciones.

Los casos de uso no deben depender directamente de los controladores HTTP ni de las implementaciones concretas de persistencia.

### 4.3. Adaptadores de entrada

Permiten que los usuarios y sistemas externos interactúen con la aplicación.

En Sports Gear estarán representados principalmente por:

- Endpoints HTTP.
- Controladores de Laravel.
- Validación de solicitudes.
- Adaptadores de autenticación y autorización.
- Transformación de solicitudes y respuestas.

Su responsabilidad es recibir solicitudes, convertirlas al formato esperado por la aplicación y devolver las respuestas correspondientes.

No deben contener las reglas fundamentales del dominio.

### 4.4. Adaptadores de salida

Implementan los mecanismos necesarios para que la aplicación interactúe con servicios externos.

Se contemplan:

- Persistencia mediante la base de datos.
- Implementaciones de los repositorios.
- Acceso a los datos.
- Servicios de infraestructura necesarios para los casos de uso.

Las implementaciones concretas deberán respetar los puertos definidos por la aplicación.

### 4.5. Composición e inicialización

Existirá un punto de configuración responsable de conectar las implementaciones de los puertos con los casos de uso.

Este componente permitirá integrar:

- Los casos de uso.
- Los repositorios.
- Los servicios de infraestructura.
- Los adaptadores HTTP.
- Los mecanismos de autenticación.

La configuración utilizará los mecanismos de Laravel, sin trasladar las reglas del dominio al framework.

## 5. Componentes principales del dominio

### 5.1. Equipment

Representa un implemento deportivo registrado en el catálogo.

Responsabilidades:

- Identificarse mediante su referencia.
- Pertenecer a una categoría.
- Mantener el total de unidades.
- Mantener las unidades disponibles.
- Controlar la disponibilidad.
- Permitir la baja lógica cuando corresponda.

Invariante principal:

`0 <= available_units <= total_units`

El implemento no podrá participar en nuevos préstamos cuando se encuentre dado de baja.

### 5.2. Category

Representa las categorías fijas de los implementos deportivos.

Categorías iniciales:

- Balones.
- Raquetas.
- Protección.
- Atletismo.
- Gimnasio.

Las categorías serán inicializadas mediante el proceso de sembrado correspondiente.

No se contempla su administración dinámica desde la aplicación.

### 5.3. Athlete

Representa al deportista que solicita implementos.

Responsabilidades:

- Mantener la información de identificación.
- Permitir su consulta.
- Relacionarse con los préstamos registrados.

El deportista no tendrá credenciales ni acceso autenticado al sistema.

### 5.4. User

Representa a los usuarios autorizados para operar el sistema.

Roles contemplados:

- Admin.
- Coach.

Responsabilidades:

- Mantener las credenciales de acceso.
- Identificar el rol del usuario.
- Permitir la autenticación.
- Restringir las operaciones según los permisos correspondientes.

### 5.5. Loan

Representa una operación de préstamo realizada a un deportista.

Responsabilidades:

- Identificar al deportista.
- Identificar al entrenador que registra la operación.
- Registrar la fecha del préstamo.
- Contener los implementos entregados.
- Coordinar las operaciones de devolución.

El préstamo debe conservar su historial.

### 5.6. LoanItem

Representa cada implemento incluido en un préstamo.

Responsabilidades:

- Identificar el implemento prestado.
- Conservar la información histórica del implemento.
- Registrar la fecha de vencimiento.
- Registrar la fecha de devolución cuando corresponda.
- Permitir determinar si el implemento continúa pendiente.

La información histórica no debe modificarse cuando cambie el catálogo.

## 6. Puertos de aplicación

La aplicación definirá contratos para las operaciones que necesiten comunicarse con elementos externos.

Los puertos permitirán mantener independientes los casos de uso de las implementaciones concretas.

Se contemplan las siguientes responsabilidades:

| Puerto | Responsabilidad |
|---|---|
| EquipmentRepository | Consultar y persistir implementos deportivos. |
| AthleteRepository | Consultar y persistir deportistas. |
| UserRepository | Consultar y persistir usuarios. |
| LoanRepository | Consultar y persistir préstamos y sus detalles. |
| TransactionManager | Coordinar operaciones transaccionales. |
| Clock | Proporcionar la fecha y hora utilizada por las reglas del sistema. |

Los contratos definitivos y sus operaciones se concretarán durante el diseño de implementación.

Las firmas de los puertos no deberán exponer directamente clases del framework ni modelos de infraestructura.

## 7. Casos de uso principales

Los casos de uso representan las operaciones que el sistema debe ejecutar.

Se contemplan:

### 7.1. Autenticación

- Iniciar sesión.
- Validar las credenciales.
- Identificar el rol del usuario.
- Autorizar el acceso a las funcionalidades correspondientes.

### 7.2. Administración de usuarios

- Registrar entrenadores.
- Consultar entrenadores.
- Aplicar restricciones según el rol.

### 7.3. Administración de implementos

- Registrar implementos.
- Consultar implementos.
- Actualizar información.
- Dar de baja implementos.
- Consultar disponibilidad.

### 7.4. Administración de deportistas

- Registrar deportistas.
- Consultar deportistas.
- Consultar información individual.

### 7.5. Préstamos

- Registrar préstamos.
- Verificar la disponibilidad.
- Verificar el límite de implementos pendientes.
- Verificar la existencia de préstamos vencidos.
- Actualizar las unidades disponibles.
- Conservar la información histórica.

### 7.6. Devoluciones

- Consultar implementos pendientes.
- Registrar devoluciones.
- Actualizar las unidades disponibles.
- Evitar devoluciones duplicadas.
- Conservar el historial de operaciones.

### 7.7. Consultas y reportes

- Consultar préstamos.
- Consultar préstamos vencidos.
- Consultar implementos más solicitados.

## 8. Flujo de registro de un préstamo

El registro de un préstamo debe coordinar el dominio, la aplicación y la persistencia.

Flujo general:

1. El entrenador envía la solicitud mediante la API.
2. El adaptador HTTP valida la estructura de la solicitud.
3. El caso de uso recibe los datos necesarios.
4. Se consulta el deportista.
5. Se verifica que el deportista no tenga préstamos vencidos pendientes.
6. Se verifica que no supere el máximo de tres implementos pendientes.
7. Se consultan los implementos solicitados.
8. Se verifica que estén activos y tengan unidades disponibles.
9. El dominio aplica las operaciones correspondientes sobre los implementos.
10. Se registra el préstamo y sus detalles.
11. Se actualizan las unidades disponibles.
12. Se confirma la transacción.
13. El adaptador HTTP devuelve la respuesta correspondiente.

Si ocurre un error durante la operación, la transacción debe revertirse para evitar registros parciales o cantidades inconsistentes.

## 9. Flujo de devolución

El registro de una devolución debe garantizar que cada implemento se devuelva una sola vez.

Flujo general:

1. El entrenador solicita registrar una devolución.
2. El adaptador HTTP valida la solicitud.
3. El caso de uso consulta el préstamo y su detalle.
4. Se verifica que el implemento pertenezca al préstamo.
5. Se verifica que no haya sido devuelto anteriormente.
6. El dominio registra la devolución.
7. Se actualizan las unidades disponibles.
8. Se confirma la transacción.
9. El adaptador HTTP devuelve la respuesta correspondiente.

Una devolución no debe eliminar ni alterar la información histórica del préstamo.

## 10. Control de concurrencia

El sistema debe proteger la disponibilidad cuando varios entrenadores intenten prestar simultáneamente un mismo implemento.

Caso crítico:

Un implemento tiene una sola unidad disponible y dos entrenadores intentan prestarla al mismo tiempo.

Resultado esperado:

- Una operación debe completarse.
- La segunda operación debe rechazarse.
- La operación rechazada debe recibir HTTP 409 Conflict.
- Las unidades disponibles no pueden quedar por debajo de cero.
- No deben generarse préstamos parciales.

La estrategia concreta de concurrencia deberá definirse mediante una decisión técnica ADR y corresponder con las capacidades del motor de base de datos seleccionado.

La implementación deberá garantizar la consistencia tanto desde la lógica de aplicación como desde la persistencia.

## 11. Manejo de errores

Los errores del dominio y de la aplicación deberán transformarse en respuestas HTTP mediante los adaptadores correspondientes.

El dominio no deberá construir respuestas HTTP ni depender de códigos de estado.

Se deberán contemplar, entre otros:

- Credenciales inválidas.
- Acceso no autorizado.
- Recurso inexistente.
- Datos inválidos.
- Implemento sin unidades disponibles.
- Deportista con préstamos vencidos.
- Límite de implementos pendientes superado.
- Devolución duplicada.
- Conflicto de concurrencia.

Las respuestas deberán respetar la estructura uniforme establecida en el contrato de la API.

Los mensajes visibles para el usuario estarán en español.

## 12. Organización del backend

El backend se organizará de forma que las responsabilidades puedan identificarse claramente.

La estructura definitiva deberá permitir separar:

- Dominio.
- Aplicación.
- Puertos.
- Adaptadores de entrada.
- Adaptadores de salida.
- Configuración.
- Infraestructura.
- Pruebas.

La organización física de directorios se definirá durante la preparación del proyecto Laravel.

No se deberá introducir una estructura adicional que no aporte una responsabilidad clara.

## 13. Frontend

La aplicación React consumirá los endpoints del backend.

Responsabilidades:

- Presentar las interfaces de usuario.
- Permitir la navegación según el rol.
- Enviar solicitudes a la API.
- Mostrar los resultados.
- Presentar los mensajes de validación y error.
- Mantener una separación entre los componentes visuales y la comunicación con la API.

React no será responsable de garantizar las reglas de negocio.

Las validaciones del frontend serán complementarias a las del backend.

## 14. Infraestructura

Docker Compose será responsable de coordinar los servicios necesarios para ejecutar el sistema.

La infraestructura deberá permitir:

- Iniciar los servicios con un solo comando.
- Configurar la conexión a la base de datos.
- Mantener la persistencia de los datos.
- Ejecutar las migraciones necesarias.
- Inicializar las categorías.
- Proporcionar la configuración requerida por el backend y el frontend.

La configuración no deberá contener credenciales reales versionadas en el repositorio.

## 15. Reglas arquitectónicas

### Regla I. Independencia del dominio

El dominio no dependerá de frameworks, ORM, controladores, servicios externos ni mecanismos de persistencia.

### Regla II. Dependencias dirigidas hacia el núcleo

La aplicación definirá los contratos que necesita y los adaptadores implementarán dichos contratos.

Las implementaciones externas no deberán controlar las reglas del dominio.

### Regla III. Punto único de composición

La conexión entre los casos de uso y las implementaciones concretas deberá realizarse mediante un punto de configuración identificable.

### Regla IV. Separación de responsabilidades

Cada componente deberá tener una responsabilidad definida.

Los controladores no deberán concentrar reglas de negocio y los repositorios no deberán asumir responsabilidades propias de los casos de uso.

### Regla V. Consistencia de persistencia

Las operaciones que modifiquen préstamos, devoluciones y disponibilidad deberán mantener la integridad de los datos.

### Regla VI. Conservación histórica

Las operaciones registradas no deberán eliminarse y la información histórica de los implementos prestados no deberá depender de cambios posteriores en el catálogo.

### Regla VII. Estados derivados

Los estados de los préstamos deberán calcularse a partir de las fechas y devoluciones registradas, evitando almacenar estados derivados innecesariamente.

## 16. Pruebas de arquitectura

La arquitectura deberá contar con pruebas que permitan verificar sus reglas.

Se deberá comprobar:

- Que el dominio no dependa de Laravel ni de Eloquent.
- Que el dominio no dependa de controladores HTTP.
- Que la aplicación dependa de contratos y no de implementaciones concretas.
- Que los adaptadores implementen las responsabilidades correspondientes.
- Que exista un punto identificable de composición.
- Que las reglas de negocio no estén concentradas en los controladores.

Las pruebas automatizadas deberán proporcionar evidencia de estas condiciones.

## 17. Relación con otros documentos

| Documento | Relación con la arquitectura |
|---|---|
| `constitution.md` | Establece los principios y reglas obligatorias del proyecto. |
| `spec.md` | Define las funcionalidades y restricciones que la arquitectura debe soportar. |
| `plan.md` | Establece las fases y la estrategia de desarrollo. |
| `data-model.md` | Define la estructura de los datos y sus relaciones. |
| `api-contract.md` | Define la comunicación HTTP entre el frontend y el backend. |
| `tasks.md` | Organiza las actividades necesarias para implementar la solución. |
| `adr/` | Registra las decisiones técnicas que afectan la arquitectura. |

## 18. Criterios de aceptación arquitectónica

La arquitectura se considerará correctamente implementada cuando:

1. Las responsabilidades estén separadas.
2. El dominio sea independiente del framework.
3. La aplicación utilice contratos para acceder a servicios externos.
4. Los adaptadores implementen los contratos correspondientes.
5. Exista un punto identificable de composición.
6. Las operaciones de préstamo y devolución sean transaccionales.
7. La concurrencia mantenga la consistencia de las unidades.
8. Las operaciones históricas se conserven.
9. Los estados derivados se calculen a partir de los datos correspondientes.
10. Las reglas arquitectónicas tengan pruebas automatizadas.