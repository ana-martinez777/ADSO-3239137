// Complementary frontend validations.
// They do not replace backend validation (docs/architecture.md, section 13):
// they only avoid sending requests the API would certainly reject.

export const MAX_PENDING_UNITS = 3;

/** Period filters: both dates or none, and from <= to. */
export function validateDateRange(from, to) {
  if (!from && !to) return null;
  if (!from || !to) return 'Para consultar un período debes indicar la fecha inicial y la fecha final.';
  if (from > to) return 'La fecha inicial no puede ser posterior a la fecha final.';
  return null;
}

/**
 * Checks a loan draft before sending it.
 * @param {{athleteId: string, items: {equipmentId: string, quantity: number|string}[]}} draft
 * @param {Map<string, {available_units: number, reference: string}>} equipmentById
 * @returns {string[]} list of error messages in Spanish
 */
export function validateLoanDraft(draft, equipmentById = new Map()) {
  const errors = [];
  if (!draft.athleteId) errors.push('Selecciona el deportista que recibe los implementos.');

  const items = draft.items.filter((item) => item.equipmentId);
  if (items.length === 0) errors.push('Agrega al menos un implemento al préstamo.');

  const ids = items.map((item) => item.equipmentId);
  if (new Set(ids).size !== ids.length) errors.push('Un mismo implemento no puede repetirse en el préstamo.');

  let total = 0;
  items.forEach((item) => {
    const quantity = Number(item.quantity);
    const equipment = equipmentById.get(item.equipmentId);
    const name = equipment?.reference ?? 'el implemento';
    if (!Number.isInteger(quantity) || quantity < 1) {
      errors.push(`La cantidad de ${name} debe ser un número entero mayor que cero.`);
      return;
    }
    total += quantity;
    if (equipment && quantity > equipment.available_units) {
      errors.push(`Solo hay ${equipment.available_units} unidad(es) disponible(s) de ${name}.`);
    }
  });

  if (total > MAX_PENDING_UNITS) {
    errors.push(`Un deportista puede tener máximo ${MAX_PENDING_UNITS} unidades pendientes; este préstamo suma ${total}.`);
  }
  return errors;
}

export function validateEquipmentForm({ reference, category_id, total_units }) {
  const errors = [];
  if (!reference?.trim()) errors.push('La referencia es obligatoria.');
  if (!category_id) errors.push('Selecciona una categoría.');
  const total = Number(total_units);
  if (!Number.isInteger(total) || total < 1) errors.push('El total de unidades debe ser un número entero mayor o igual a 1.');
  return errors;
}

export function validateAthleteForm({ full_name, document_number }) {
  const errors = [];
  if (!full_name?.trim()) errors.push('El nombre completo es obligatorio.');
  if (!document_number?.trim()) errors.push('El número de documento es obligatorio.');
  return errors;
}

export function validateCoachForm({ username, password, passwordConfirmation }) {
  const errors = [];
  if (!username?.trim()) errors.push('El nombre de usuario es obligatorio.');
  if (!password) errors.push('La contraseña es obligatoria.');
  if (password && password !== passwordConfirmation) errors.push('Las contraseñas no coinciden.');
  return errors;
}
