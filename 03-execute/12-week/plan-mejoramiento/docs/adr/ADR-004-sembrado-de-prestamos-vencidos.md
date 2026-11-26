# ADR-004: Sembrado de préstamos vencidos mediante un reloj desplazado

## Estado

Aceptado.

## Contexto

La tarea T-21 exige que la herramienta de sembrado registre, mediante la API, al menos 15 préstamos, de los cuales al menos 3 deben estar vencidos.

La API registra cada préstamo con el instante actual y calcula el vencimiento 48 horas después. No existe un endpoint que permita indicar la fecha de un préstamo, y agregarlo incorporaría una funcionalidad que no está definida en `spec.md` ni en `api-contract.md`.

`plan.md` establece además que la herramienta no puede conectarse directamente a la base de datos para insertar registros.

`architecture.md` define el puerto `Clock` como el responsable de proporcionar la fecha y hora que utilizan las reglas del sistema. El adaptador de producción, `SystemClock`, toma la hora del sistema operativo en UTC.

## Decisión

Los préstamos vencidos se registrarán mediante la API, en una instancia temporal cuyo reloj del sistema operativo se encuentra 3 días atrás.

- La instancia `api-past` utiliza la misma imagen y el mismo código que la API, sin cambios.
- El desplazamiento se aplica en la infraestructura con `libfaketime` (`FAKETIME=-3d`). `SystemClock` lee esa hora sin saber que está desplazada.
- `api-past` y la herramienta de sembrado pertenecen al perfil `seed` de Docker Compose. No se inician con `docker compose up` y no publican puertos en el equipo.
- La herramienta registra en `api-past` tres préstamos de una unidad. Su fecha de préstamo queda 3 días atrás y su vencimiento, 1 día atrás.
- El resto de los datos se registra en la API normal.

## Alternativas consideradas

- **Insertar los préstamos directamente en MySQL:** descartada porque `plan.md` exige que el sembrado utilice la API.
- **Agregar un parámetro de fecha al registro de préstamos:** descartada porque es una funcionalidad fuera del spec y permitiría registrar préstamos con fechas arbitrarias.
- **Permitir configurar un desfase en `SystemClock` mediante una variable de entorno:** descartada porque agrega a la API una configuración que el spec no define.
- **Esperar 48 horas después del sembrado:** descartada porque no permite preparar los datos de demostración en un solo paso.

## Consecuencias

### Positivas

- Todos los datos de demostración pasan por las validaciones y reglas de la API.
- El código de la API no contiene lógica específica para el sembrado.
- La instancia con el reloj desplazado solo existe mientras se ejecuta el sembrado.

### Negativas

- La imagen de la API incluye `libfaketime`, aunque solo `api-past` lo activa.
- El sembrado depende de Docker Compose para obtener los préstamos vencidos.
