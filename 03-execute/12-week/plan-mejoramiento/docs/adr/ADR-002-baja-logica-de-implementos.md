
# ADR-002: Baja lógica de implementos

## Estado

Aceptado.

## Contexto

Sports Gear permite administrar el catálogo de implementos deportivos y registrar préstamos y devoluciones asociados a ellos.

Un implemento puede tener movimientos históricos que deben conservarse para mantener la trazabilidad de las operaciones.

La eliminación física de un implemento podría afectar la integridad de las relaciones con los préstamos y sus detalles. Además, un implemento que ya no está disponible para nuevas operaciones puede seguir siendo necesario para consultar el historial o registrar una devolución pendiente.

## Decisión

Los implementos se retirarán del catálogo mediante baja lógica, utilizando el campo `deleted_at`.

Cuando un implemento sea dado de baja:

1. Se registrará la fecha de baja en `deleted_at`.
2. El implemento dejará de estar disponible para nuevos préstamos.
3. Sus datos y relaciones históricas se conservarán.
4. Se permitirán las devoluciones de préstamos pendientes asociados al implemento.
5. No se permitirá reactivar un implemento dado de baja.
6. Su referencia seguirá reservada y no podrá reutilizarse para registrar otro implemento.

La reserva permanente de referencias se implementará mediante la tabla `EquipmentReferenceHistory`, que conservará cada referencia asignada a un implemento.

La tabla tendrá `reference` como clave primaria y `equipment_id` como clave foránea hacia `Equipment`.

Cuando se registre un implemento, su primera referencia se almacenará en el historial dentro de la misma transacción.

Cuando se modifique la referencia actual, la nueva referencia se insertará como un registro adicional. Los registros anteriores permanecerán inmutables y no podrán eliminarse ni reutilizarse.

La actualización de la referencia actual y el registro de la nueva referencia deben confirmarse en una sola transacción.

La baja lógica estará disponible únicamente para el administrador.

## Alternativas consideradas

- **Eliminación física:** descartada porque podría comprometer la conservación del historial y la integridad de las relaciones con los préstamos.
- **Baja lógica con posibilidad de reactivación:** descartada porque el proyecto no contempla una funcionalidad de reactivación.
- **Baja lógica sin reactivación:** seleccionada porque permite retirar implementos del catálogo sin perder la información histórica.

## Consecuencias

### Positivas

- Se conserva el historial de préstamos y devoluciones.
- Se mantiene la integridad referencial de los registros.
- Los implementos dados de baja no pueden utilizarse en nuevos préstamos.
- Las devoluciones pendientes pueden completarse.
- Se evita reutilizar referencias de implementos dados de baja.
- Se conserva el historial completo de referencias asignadas a cada implemento.
- La clave primaria de EquipmentReferenceHistory impide reutilizar referencias anteriores.
- La reserva de referencias se mantiene incluso cuando el implemento cambia su referencia actual o se da de baja.

### Consideraciones

- Las consultas del catálogo deben distinguir entre implementos activos e inactivos según el rol del usuario.
- El administrador puede consultar implementos activos, inactivos o todos.
- El entrenador solamente puede consultar implementos activos.
- La baja lógica no debe modificar ni eliminar los registros históricos de préstamos y sus detalles.
- La creación de implementos y los cambios de referencia deben registrar el historial dentro de la misma transacción que modifica Equipment.
- Los registros de EquipmentReferenceHistory son permanentes e inmutables.
- La validación de una referencia nueva debe consultar el historial completo, no solamente la referencia actual del catálogo.

## Referencias

- `docs/spec.md`: HU-03 y HU-04; RN-05.
- `docs/data-model.md`: entidad Equipment y sus reglas de baja lógica.
- `docs/api-contract.md`: endpoints de consulta y baja lógica de implementos.
- `docs/architecture.md`: componentes y responsabilidades del dominio.
