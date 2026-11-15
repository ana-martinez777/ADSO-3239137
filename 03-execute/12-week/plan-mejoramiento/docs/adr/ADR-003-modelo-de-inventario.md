
# ADR-003: Modelo de inventario y conservación de unidades prestadas

## Estado

Aceptado.

## Contexto

Sports Gear administra implementos deportivos que pueden tener varias unidades disponibles para préstamo.

El sistema debe mantener la consistencia entre las unidades totales, las unidades disponibles y las unidades que actualmente se encuentran prestadas.

Cuando se registra un préstamo, las unidades disponibles disminuyen. Cuando se registra una devolución, las unidades disponibles aumentan.

También es necesario permitir que el administrador modifique la cantidad total de unidades de un implemento sin alterar incorrectamente las unidades que se encuentran prestadas.

## Decisión

Se mantendrán los campos `total_units` y `available_units` en la entidad Equipment.

Las unidades actualmente prestadas se determinarán mediante la diferencia entre las unidades totales y las disponibles:

`loaned_units = total_units - available_units`

Al modificar la cantidad total de unidades, la disponibilidad se recalculará conservando las unidades que actualmente están prestadas:

`available_units = new_total_units - loaned_units`

### Reglas de actualización

1. La cantidad total nueva debe ser igual o superior a una unidad.
2. No se permitirá establecer una cantidad total inferior a las unidades que actualmente están prestadas.
3. La disponibilidad se recalculará automáticamente después de validar la nueva cantidad total.
4. La actualización se realizará dentro de una transacción y bloqueará el registro del implemento mediante `SELECT ... FOR UPDATE`.
5. La cantidad disponible nunca podrá ser inferior a cero ni superar la cantidad total.

### Ejemplo

Un implemento tiene:

- Unidades totales: 10.
- Unidades prestadas: 3.
- Unidades disponibles: 7.

Si el administrador modifica las unidades totales de 10 a 12, el sistema conservará las 3 unidades prestadas y recalculará la disponibilidad:

- Unidades totales nuevas: 12.
- Unidades prestadas: 3.
- Unidades disponibles nuevas: 9.

Si el administrador intenta reducir las unidades totales a 2, la operación será rechazada porque existen 3 unidades prestadas.

## Alternativas consideradas

- **Modificar únicamente las unidades totales:** descartada porque podría dejar una disponibilidad inconsistente respecto de las unidades prestadas.
- **Permitir modificar manualmente las unidades disponibles:** descartada porque podría provocar diferencias entre el inventario y los movimientos registrados.
- **Recalcular automáticamente la disponibilidad conservando las unidades prestadas:** seleccionada porque mantiene la consistencia del inventario y permite actualizar las cantidades totales sin alterar los préstamos pendientes.

## Consecuencias

### Positivas

- Se conserva la relación entre unidades totales, disponibles y prestadas.
- Se evita registrar una disponibilidad negativa.
- Se impide reducir el inventario por debajo de las unidades que están prestadas.
- Se mantiene la consistencia durante las actualizaciones concurrentes mediante transacciones y bloqueos de filas.
- Se simplifica la validación de las cantidades del inventario.

### Consideraciones

- La disponibilidad no debe modificarse independientemente de los movimientos de préstamo, devolución o actualización de unidades totales.
- Las operaciones de préstamo y devolución deben actualizar la disponibilidad dentro de sus respectivas transacciones.
- Las restricciones de integridad de MySQL deben garantizar que `total_units` sea mayor o igual a uno y que `available_units` permanezca entre cero y `total_units`.
- Las reglas de negocio deben verificarse mediante pruebas automatizadas.

## Referencias

- `docs/spec.md`: HU-03 y RN-01.
- `docs/data-model.md`: entidad Equipment, restricciones de inventario y actualización de unidades totales.
- `docs/tasks.md`: T-04, T-08 y T-18.
- `docs/architecture.md`: responsabilidades del dominio Equipment.
