# Plan de desarrollo — Sports Gear

## 1. Información general

| Campo | Descripción |
|---|---|
| Proyecto | Sports Gear |
| Identificador del dominio | sports-gear |
| Tipo de desarrollo | Aplicación web |
| Frontend | React |
| Backend | PHP con Laravel |
| Persistencia | Base de datos relacional |
| Infraestructura | Docker Compose |
| Metodología técnica | Desarrollo guiado por especificaciones |

## 2. Objetivo del plan

Establecer la estrategia técnica y el orden de ejecución para construir Sports Gear, garantizando que el desarrollo corresponda con los requisitos funcionales, las reglas de negocio y los requisitos no funcionales definidos en `spec.md`.

El desarrollo se organizará por componentes y responsabilidades, manteniendo separadas la lógica del dominio, la aplicación, la infraestructura y las interfaces de usuario.

## 3. Principios de desarrollo

El desarrollo seguirá estos principios:

1. La especificación funcional será la referencia para determinar el alcance.
2. La lógica del dominio no dependerá directamente de Laravel, React ni del motor de base de datos.
3. Las responsabilidades se separarán para facilitar el mantenimiento y las pruebas.
4. Las operaciones de préstamo y devolución deberán mantener la consistencia de las unidades disponibles.
5. Las reglas de negocio se verificarán antes de confirmar las operaciones.
6. Las migraciones de Laravel serán responsables de definir y evolucionar la estructura de la base de datos.
7. Las restricciones que puedan garantizarse mediante la base de datos deberán respaldarse con mecanismos de persistencia.
8. Las pruebas automatizadas deberán proporcionar evidencia del comportamiento esperado.
9. La documentación deberá mantenerse alineada con el código implementado.
10. No se desarrollarán funcionalidades que no estén contempladas en la especificación.

## 4. Organización del proyecto

El proyecto se organizará en las siguientes carpetas:

| Carpeta | Responsabilidad |
|---|---|
| `docs/` | Especificaciones, arquitectura, modelo de datos, contrato API, tareas y decisiones técnicas. |
| `api/` | Backend desarrollado con PHP y Laravel. |
| `app/` | Aplicación frontend desarrollada con React. |
| `page/` | Sitio público estático de presentación del producto. |
| `infra/` | Configuración de contenedores y servicios de infraestructura. |
| `tool/` | Herramienta para sembrar datos de prueba mediante la API. |

Cada componente tendrá una responsabilidad definida y no deberá asumir funciones pertenecientes a otro componente.

## 5. Estrategia de arquitectura

Se utilizará una arquitectura basada en separación de responsabilidades y principios de arquitectura hexagonal.

La organización lógica del backend contemplará:

- Dominio: entidades, reglas de negocio y comportamientos propios del sistema.
- Aplicación: casos de uso y coordinación de operaciones.
- Adaptadores de entrada: controladores y puntos de acceso HTTP.
- Adaptadores de salida: persistencia y servicios externos necesarios.
- Composición: configuración e integración de los componentes.

Las dependencias deberán dirigirse hacia el núcleo del dominio.

Laravel se utilizará como framework del backend, pero las reglas fundamentales del dominio no deberán depender directamente de sus mecanismos de infraestructura.

La arquitectura detallada se documentará en `architecture.md`.

## 6. Estrategia de persistencia

La información se almacenará en una base de datos relacional.

La estructura de persistencia deberá representar, como mínimo, la información necesaria para:

- Usuarios y roles.
- Categorías de implementos.
- Catálogo de implementos.
- Deportistas.
- Préstamos.
- Detalle de los implementos incluidos en cada préstamo.

Las migraciones de Laravel serán la fuente de definición de la estructura de la base de datos.

Se deberán contemplar:

- Claves primarias.
- Relaciones entre entidades.
- Restricciones de integridad.
- Unicidad de los datos que lo requieran.
- Control de unidades disponibles.
- Conservación de las operaciones históricas.
- Baja lógica de implementos.

Los atributos definitivos y las relaciones se especificarán en `data-model.md`.

El motor de base de datos seleccionado para Sports Gear es MySQL.

La configuración de conexión, las migraciones y las restricciones de integridad deberán ser compatibles con MySQL.

La versión específica del motor se establecerá durante la configuración de infraestructura y quedará documentada en la decisión técnica correspondiente.

## 7. Estrategia de desarrollo del backend

El backend se implementará con PHP y Laravel.

Su responsabilidad será:

- Gestionar la autenticación y autorización.
- Exponer los endpoints de la API.
- Validar las solicitudes recibidas.
- Ejecutar los casos de uso.
- Aplicar las reglas del dominio.
- Gestionar las operaciones de persistencia.
- Controlar las transacciones.
- Proporcionar respuestas HTTP uniformes.

Los controladores no deberán concentrar las reglas de negocio.

Las operaciones de préstamo y devolución deberán ejecutarse de manera transaccional para evitar inconsistencias.

La definición de rutas, parámetros, respuestas y errores se establecerá en `api-contract.md`.

## 8. Estrategia de desarrollo del frontend

El frontend se implementará con React.

Su responsabilidad será proporcionar las interfaces necesarias para los usuarios del sistema.

Contemplará las funcionalidades correspondientes a:

- Inicio de sesión.
- Administración de implementos.
- Consulta del catálogo y disponibilidad.
- Gestión de deportistas.
- Gestión de entrenadores.
- Registro de préstamos.
- Registro de devoluciones.
- Consulta de préstamos.
- Consulta de préstamos vencidos.
- Reporte de implementos más solicitados.

La interfaz deberá respetar los permisos correspondientes a los roles de administrador y entrenador.

Las validaciones visuales no reemplazarán las validaciones del backend.

Los mensajes visibles para los usuarios estarán en español.

## 9. Estrategia de infraestructura

La infraestructura se organizará mediante Docker Compose.

Deberá permitir iniciar los servicios necesarios mediante un único comando.

Se contemplarán:

- Servicio del backend.
- Servicio del frontend cuando corresponda a la configuración de ejecución.
- Servicio de base de datos.
- Configuración de variables de entorno.
- Persistencia de los datos de la base de datos.
- Inicialización mediante migraciones.
- Configuración necesaria para ejecutar el sistema.

La configuración deberá permitir iniciar el proyecto con una base de datos vacía.

Los archivos que contengan credenciales o secretos no deberán incluirse en el repositorio.

Se proporcionará un archivo de ejemplo de variables de entorno sin valores secretos.

## 10. Estrategia de sembrado de datos

La carpeta `tool/` contendrá una herramienta encargada de crear datos de prueba mediante la API.

La herramienta no deberá conectarse directamente a la base de datos para insertar los registros.

Las operaciones de sembrado utilizarán los endpoints definidos en el contrato de la API.

Los datos de prueba deberán permitir verificar:

- Las cinco categorías iniciales.
- Los implementos deportivos.
- Los deportistas.
- Los usuarios necesarios para las pruebas.
- Los préstamos registrados.
- Los préstamos vencidos.
- Las operaciones de devolución.

La cantidad y distribución definitiva de los datos de prueba deberán corresponder con los criterios establecidos para la herramienta de sembrado.

## 11. Fases de desarrollo

### Fase 1. Preparación documental y técnica

Objetivo: establecer las bases del proyecto antes de implementar las funcionalidades.

Actividades:

- Definir la constitución del proyecto.
- Establecer la especificación funcional.
- Definir el plan de desarrollo.
- Documentar la arquitectura.
- Definir el modelo de datos.
- Establecer el contrato de la API.
- Registrar las decisiones técnicas mediante ADR.
- Definir las tareas de implementación.

Resultado esperado: documentación técnica coherente y lista para orientar el desarrollo.

### Fase 2. Preparación del entorno

Objetivo: preparar la estructura de ejecución del proyecto.

Actividades:

- Crear la estructura inicial de Laravel.
- Preparar la estructura de React.
- Configurar la base de datos.
- Definir las variables de entorno.
- Preparar Docker Compose.
- Implementar las migraciones iniciales.
- Verificar el inicio de los servicios.

Resultado esperado: entorno de desarrollo ejecutable y estructura inicial preparada.

### Fase 3. Implementación del dominio

Objetivo: implementar las reglas fundamentales de Sports Gear.

Actividades:

- Implementar el comportamiento de los implementos.
- Implementar el control de unidades.
- Implementar el comportamiento de los deportistas.
- Implementar la gestión de usuarios y roles.
- Implementar el registro de préstamos.
- Implementar el registro de devoluciones.
- Implementar el cálculo de vencimientos.
- Garantizar la conservación de los datos históricos.

Resultado esperado: reglas del dominio implementadas y verificables mediante pruebas.

### Fase 4. Implementación de la API

Objetivo: exponer las funcionalidades del dominio mediante endpoints HTTP.

Actividades:

- Implementar autenticación.
- Implementar autorización por roles.
- Implementar operaciones del catálogo.
- Implementar operaciones de deportistas.
- Implementar operaciones de entrenadores.
- Implementar operaciones de préstamos.
- Implementar operaciones de devoluciones.
- Implementar consulta de préstamos vencidos.
- Implementar el reporte de implementos más solicitados.
- Estandarizar las respuestas y errores HTTP.

Resultado esperado: API funcional conforme al contrato documentado.

### Fase 5. Implementación del frontend

Objetivo: construir las interfaces de usuario conectadas con la API.

Actividades:

- Implementar inicio de sesión.
- Implementar navegación según el rol.
- Implementar administración del catálogo.
- Implementar gestión de deportistas.
- Implementar gestión de entrenadores.
- Implementar registro y consulta de préstamos.
- Implementar registro de devoluciones.
- Implementar consulta de préstamos vencidos.
- Implementar el reporte correspondiente.

Resultado esperado: aplicación web funcional para administradores y entrenadores.

### Fase 6. Integración y pruebas

Objetivo: verificar el comportamiento completo del sistema.

Actividades:

- Ejecutar pruebas unitarias.
- Ejecutar pruebas de integración.
- Verificar las reglas de negocio.
- Verificar las restricciones de persistencia.
- Verificar la autenticación y autorización.
- Verificar las respuestas de error.
- Verificar la concurrencia sobre la última unidad.
- Verificar el funcionamiento mediante Docker Compose.
- Verificar el sembrado de datos mediante la API.

Resultado esperado: evidencia automatizada y funcional del cumplimiento de los requisitos.

### Fase 7. Sitio público y entrega

Objetivo: completar los elementos de presentación y preparar el producto.

Actividades:

- Revisar el sitio público estático.
- Verificar que presente Sports Gear y sus funcionalidades.
- Revisar la documentación.
- Verificar la correspondencia entre especificación, arquitectura y código.
- Revisar la estructura del repositorio.
- Verificar los archivos de configuración y exclusión.
- Preparar la entrega del proyecto.

Resultado esperado: producto integrado, documentado y preparado para revisión.

## 12. Estrategia de pruebas

Las pruebas deberán verificar el comportamiento del sistema desde diferentes niveles.

### 12.1. Pruebas unitarias

Verificarán los comportamientos y reglas del dominio sin depender de servicios externos.

### 12.2. Pruebas de aplicación

Verificarán que los casos de uso coordinen correctamente las operaciones y respeten las reglas de negocio.

### 12.3. Pruebas de integración

Verificarán la interacción entre la aplicación, Laravel, la persistencia y los componentes integrados.

### 12.4. Pruebas de API

Verificarán los endpoints, los permisos, los datos recibidos, las respuestas y los errores.

### 12.5. Pruebas de concurrencia

Verificarán que dos operaciones simultáneas sobre la última unidad disponible no produzcan una disponibilidad inconsistente.

### 12.6. Pruebas de arquitectura

Verificarán que las dependencias y responsabilidades respeten las reglas arquitectónicas documentadas.

La evidencia de pruebas deberá mantenerse dentro del proyecto y corresponder con las funcionalidades implementadas.

## 13. Gestión de decisiones técnicas

Las decisiones que afecten la arquitectura, la persistencia, la seguridad o el comportamiento del sistema deberán documentarse mediante registros ADR.

Cada decisión deberá incluir:

- Contexto.
- Problema que requiere una decisión.
- Alternativas consideradas.
- Decisión adoptada.
- Consecuencias de la decisión.

Las decisiones documentadas deberán corresponder con la implementación real.

## 14. Gestión de cambios

Los cambios de alcance deberán evaluarse frente a `spec.md`.

No se incorporarán funcionalidades adicionales sin una justificación respaldada por los requisitos del proyecto.

Cuando una decisión técnica modifique la arquitectura, el modelo de datos o el contrato de la API, se deberán actualizar los documentos afectados.

Los cambios deberán realizarse mediante commits pequeños y descriptivos.

No se deberá reescribir el historial de Git.

## 15. Criterios de finalización

El desarrollo se considerará terminado cuando:

1. Las funcionalidades establecidas en `spec.md` estén implementadas.
2. Las reglas de negocio estén verificadas.
3. Los requisitos no funcionales cuenten con evidencia.
4. La API corresponda con su contrato.
5. El frontend esté integrado con el backend.
6. La base de datos corresponda con el modelo documentado.
7. La infraestructura permita iniciar el sistema mediante un único comando.
8. La herramienta de sembrado utilice la API.
9. Las pruebas automatizadas estén disponibles y ejecutables.
10. La documentación corresponda con el producto implementado.
11. El sitio público esté disponible como presentación independiente del sistema.
12. El repositorio esté preparado para su revisión y entrega.