# ADR-001: Control de concurrencia en las operaciones de inventario

## Estado

Aceptado.

## Contexto

El sistema Sports Gear permite registrar préstamos y devoluciones de implementos deportivos. Estas operaciones modifican las unidades disponibles del inventario.

Cuando dos entrenadores intentan prestar simultáneamente las últimas unidades de un mismo implemento, el sistema debe garantizar que no se asignen unidades por encima de las disponibles.

También es necesario evitar inconsistencias cuando se registran devoluciones o se modifica la cantidad total de unidades de un implemento.

La solución debe utilizar las capacidades del motor de base de datos seleccionado para el proyecto: MySQL 8.4 con InnoDB.

## Decisión

Se utilizarán transacciones de base de datos y bloqueos de filas mediante `SELECT ... FOR UPDATE` para controlar la concurrencia en las operaciones que modifican el inventario.

### Registro de préstamos

La operación debe:

1. Iniciar una transacción.
2. Bloquear el registro del deportista.
3. Verificar que no tenga préstamos vencidos pendientes.
4. Consultar sus unidades pendientes de devolución.
5. Verificar que el nuevo préstamo no supere el límite de tres unidades pendientes.
6. Bloquear los registros de los implementos involucrados mediante `SELECT ... FOR UPDATE`, siguiendo un orden determinista.
7. Verificar que los implementos estén activos y tengan unidades suficientes.
8. Registrar el préstamo y sus detalles.
9. Descontar las unidades correspondientes de la disponibilidad.
10. Confirmar la transacción.

Si alguna validación falla, la operación debe revertirse.

### Registro de devoluciones

La operación debe:

1. Iniciar una transacción.
2. Bloquear el detalle del préstamo mediante `SELECT ... FOR UPDATE`.
3. Verificar que el detalle todavía esté pendiente de devolución.
4. Bloquear el registro del implemento asociado.
5. Registrar la fecha de devolución.
6. Incrementar las unidades disponibles.
7. Confirmar la transacción.

Si ocurre un error, la operación debe revertirse.

### Actualización de unidades totales

La modificación de las unidades totales de un implemento también debe ejecutarse dentro de una transacción y bloquear el registro correspondiente.

La disponibilidad se recalcula conservando las unidades que actualmente están prestadas:

`available_units = new_total_units - loaned_units`

No se permite establecer un total inferior a las unidades prestadas.

## Alternativas consideradas

- **Actualización sin bloqueos:** descartada porque varias operaciones concurrentes podrían modificar las mismas unidades y producir inconsistencias.
- **Control exclusivo desde la aplicación:** descartado porque no garantiza por sí solo la exclusión entre transacciones concurrentes.
- **Bloqueos de filas con SELECT ... FOR UPDATE:** seleccionado porque permite proteger los registros involucrados durante las operaciones transaccionales utilizando MySQL e InnoDB.

## Consecuencias

### Positivas

- Evita asignar más unidades de las disponibles.
- Protege la consistencia del inventario.
- Evita superar el límite de tres unidades pendientes por deportista ante operaciones concurrentes.
- Evita registrar dos veces la devolución de un mismo detalle.
- Permite revertir operaciones incompletas mediante transacciones.

### Consideraciones

- Las operaciones deben mantener un orden determinista al bloquear varios implementos para reducir el riesgo de interbloqueos.
- Los conflictos de disponibilidad deben traducirse en el adaptador HTTP a una respuesta `409 Conflict` con el código `EQUIPMENT_NOT_AVAILABLE`.
- Las pruebas deben verificar el comportamiento de las operaciones concurrentes utilizando la base de datos real.

## Referencias

- `docs/data-model.md`: sección 7, Concurrencia y transacciones.
- `docs/architecture.md`: sección 10, Control de concurrencia.
- `docs/api-contract.md`: respuesta de conflicto `409 Conflict` para disponibilidad de implementos.