# Constitución — Sports Gear

Principios innegociables del proyecto. Cada artículo establece una regla técnica o funcional y cómo se verifica su cumplimiento.

Un cambio que viole alguno de estos principios debe corregirse antes de continuar con el desarrollo.

---

## Artículo I — El dominio no depende de nada

El dominio de Sports Gear debe permanecer independiente de los frameworks, la base de datos y los mecanismos de presentación.

Las reglas de negocio no deben depender directamente de Laravel, React, controladores HTTP, modelos de persistencia ni librerías externas.

El dominio debe permitir probar sus invariantes sin iniciar el servidor, conectarse a la base de datos o ejecutar el frontend.

**Por qué:** permite mantener las reglas de negocio independientes de la infraestructura y facilita su comprobación mediante pruebas automatizadas.

**Verificación:** comprobar que las clases y componentes que contienen las reglas del dominio no dependan directamente de los componentes de infraestructura o presentación.

## Artículo II — El núcleo declara sus puertos; el mundo los implementa

La lógica de aplicación debe definir los contratos que necesita para comunicarse con elementos externos, como repositorios, reloj del sistema y servicios de autenticación.

La infraestructura implementa esos contratos mediante los mecanismos correspondientes de Laravel y la base de datos.

Los contratos de aplicación no deben depender directamente de clases específicas del framework o del motor de persistencia.

**Por qué:** permite sustituir implementaciones externas sin modificar las reglas de negocio.

**Verificación:** comprobar que la lógica de aplicación pueda probarse mediante implementaciones simuladas (fakes) sin depender de la infraestructura real.

## Artículo III — Un único punto de composición

Las dependencias de la aplicación deben conectarse en un lugar definido de la arquitectura.

La configuración de servicios y sus implementaciones debe centralizarse mediante los mecanismos de composición e inyección de dependencias de Laravel.

No se deben crear dependencias de infraestructura de manera descontrolada dentro de las reglas del dominio.

**Por qué:** centralizar la composición facilita comprender, modificar y probar las dependencias del sistema.

**Verificación:** revisar los proveedores de servicios y la configuración de dependencias de Laravel para comprobar que las implementaciones estén registradas de forma coherente.

## Artículo IV — SOLID no es decorativo

Los principios SOLID deben aplicarse de manera concreta en el diseño del sistema.

| Principio | Aplicación en Sports Gear | Verificación |
|---|---|---|
| SRP | Cada servicio o clase debe tener una responsabilidad definida. Un caso de uso no debe asumir responsabilidades ajenas. | Revisar que las clases no tengan múltiples razones independientes para cambiar. |
| OCP | Las implementaciones deben permitir incorporar cambios sin modificar innecesariamente las reglas existentes. | Comprobar que los cambios en adaptadores no obliguen a modificar el dominio. |
| LSP | Las implementaciones de una misma abstracción deben poder sustituirse sin alterar el comportamiento esperado. | Ejecutar las mismas pruebas sobre implementaciones reales y simuladas cuando corresponda. |
| ISP | Los contratos deben ser pequeños y específicos para las necesidades de quienes los utilizan. | Comprobar que las implementaciones no estén obligadas a implementar operaciones que no necesitan. |
| DIP | Los componentes de alto nivel deben depender de abstracciones y no de implementaciones concretas. | Revisar que los contratos estén definidos en la capa que los necesita. |

**Por qué:** facilita el mantenimiento, las pruebas y la evolución del sistema.

**Verificación:** revisar la arquitectura y las pruebas automatizadas de los componentes.

## Artículo V — El esquema es propiedad de las migraciones

Las migraciones de Laravel son responsables de definir y evolucionar el esquema de la base de datos.

La infraestructura Docker debe proporcionar el motor de base de datos, pero no debe contener definiciones independientes de las tablas y restricciones que corresponden a las migraciones.

La base de datos debe poder iniciarse vacía y alcanzar el esquema requerido mediante las migraciones de la aplicación.

**Por qué:** evita mantener dos definiciones diferentes de la estructura de la base de datos.

**Verificación:** comprobar que las migraciones de Laravel definan el esquema y que la infraestructura no mantenga definiciones duplicadas de las tablas.

## Artículo VI — Las invariantes que la base puede imponer, la base las impone

Las reglas de integridad que puedan garantizarse mediante el motor de base de datos deben declararse también en el esquema.

Se deben utilizar las restricciones correspondientes, como:

- CHECK.
- UNIQUE.
- FOREIGN KEY.
- NOT NULL.

Las reglas que no puedan garantizarse directamente mediante el esquema deben protegerse mediante la lógica de aplicación y pruebas automatizadas.

**Por qué:** una regla que solamente existe en el código no protege completamente los datos frente a operaciones inconsistentes.

**Verificación:** comparar las restricciones del modelo de datos con las migraciones y comprobar su cumplimiento mediante pruebas.

## Artículo VII — Los estados derivados se calculan y los contadores operativos se mantienen de forma consistente

Los estados que puedan obtenerse a partir de la información registrada deben calcularse cuando corresponda, evitando almacenar información redundante que pueda quedar inconsistente.

El estado de un préstamo debe determinarse de acuerdo con las fechas, el plazo de dos días y la información de devolución.

La condición de vencido debe calcularse considerando el momento actual en UTC y la fecha de vencimiento.

La disponibilidad de los implementos constituye una excepción operativa: el campo available_units se mantiene almacenado para controlar el inventario y debe actualizarse transaccionalmente durante los préstamos, las devoluciones y las modificaciones de unidades totales.

**Por qué:** evita inconsistencias entre los estados derivados y la información almacenada, manteniendo al mismo tiempo un control consistente de las unidades disponibles.

**Verificación:** comprobar que los estados de los préstamos se calculen a partir de los datos correspondientes y que available_units se mantenga dentro de los límites definidos por el modelo de datos.

## Artículo VIII — Los valores congelados no siguen al catálogo

La información histórica almacenada en la línea de una operación debe conservar los valores correspondientes al momento en que se registró el préstamo.

Las modificaciones posteriores del catálogo no deben alterar los datos históricos de las operaciones.

Los reportes deben utilizar la información histórica correspondiente cuando esta sea necesaria, sin reemplazarla por los valores actuales del catálogo.

**Por qué:** garantiza la integridad histórica de los préstamos y sus devoluciones.

**Verificación:** registrar un préstamo, modificar posteriormente la información del implemento y comprobar que los datos congelados de la operación permanecen iguales.

## Artículo IX — Ningún secreto entra al repositorio ni a un log

Las contraseñas, claves de acceso, tokens y demás secretos de configuración no deben almacenarse en el repositorio público ni exponerse en registros de aplicación o respuestas HTTP.

Los valores sensibles deben obtenerse mediante variables de entorno o mecanismos seguros de configuración.

El archivo `.env` real debe permanecer excluido del control de versiones. Únicamente se podrá versionar un `.env.example` sin credenciales reales.

Tampoco deben incluirse dependencias instaladas como `node_modules` en el repositorio.

**Por qué:** evita exponer credenciales y archivos que no deben formar parte de la entrega.

**Verificación:** revisar los archivos versionados, el archivo `.gitignore`, los registros y las respuestas HTTP. Comprobar que no existan secretos publicados.

## Artículo X — Gana el motor

La base de datos es la autoridad sobre la estructura y las restricciones que realmente tiene aplicadas.

Si la documentación del modelo de datos presenta una diferencia con el esquema implementado, se debe identificar y corregir la discrepancia.

La documentación no debe afirmar que existe una restricción que realmente no está implementada.

**Por qué:** garantiza que la documentación describa el comportamiento real de la persistencia.

**Verificación:** comparar el modelo de datos con las migraciones y la estructura efectiva de la base de datos.

## Artículo XI — Idioma e identificadores

Los identificadores del código deben escribirse en inglés.

Esto incluye:

- Nombres de variables.
- Funciones y métodos.
- Clases.
- Propiedades.
- Tablas y columnas.
- Rutas e identificadores técnicos.

Los textos visibles para el usuario deben estar en español.

**Por qué:** mantiene una convención uniforme en el código y cumple las condiciones lingüísticas establecidas para el plan de mejoramiento.

**Verificación:** revisar el código fuente, los nombres de los elementos técnicos, las respuestas de la API y los textos de la interfaz.

## Artículo XII — Nada se da por hecho sin evidencia

Una tarea de `tasks.md` se considera terminada cuando existe evidencia verificable de su cumplimiento.

La existencia de código no demuestra por sí sola que una funcionalidad esté terminada.

Las evidencias pueden incluir:

- Pruebas automatizadas ejecutables.
- Verificación de reglas de negocio.
- Comprobación de endpoints.
- Ejecución correcta de la aplicación.
- Verificación de infraestructura.
- Documentación actualizada.

El proyecto debe cumplir las 10 historias de usuario, las 6 reglas de negocio, los 5 requisitos no funcionales y las 23 tareas establecidas en la especificación.

**Por qué:** permite comprobar objetivamente el avance y evita declarar terminadas funcionalidades que no han sido verificadas.

**Verificación:** revisar las evidencias asociadas a cada tarea y ejecutar las pruebas correspondientes.

---

## Cumplimiento de la constitución

Todos los cambios realizados en Sports Gear deben respetar estos principios.

Las decisiones arquitectónicas se documentarán mediante ADR en la carpeta `adr/`.

La implementación deberá mantener congruencia con los siguientes documentos:

- `spec.md`
- `plan.md`
- `architecture.md`
- `data-model.md`
- `api-contract.md`
- `tasks.md`

Cualquier modificación que afecte un principio, una regla de negocio o una decisión arquitectónica deberá revisarse y documentarse antes de considerarse terminada.