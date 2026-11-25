// Utilidades de presentación: textos en español y fechas legibles.

export const LOAN_STATUS_LABELS = Object.freeze({
  pending: 'Pendiente',
  overdue: 'Vencido',
  returned: 'Devuelto',
});

export const FIELD_LABELS = Object.freeze({
  username: 'Nombre de usuario',
  password: 'Contraseña',
  reference: 'Referencia',
  category_id: 'Categoría',
  total_units: 'Total de unidades',
  full_name: 'Nombre completo',
  document_number: 'Número de documento',
  athlete_id: 'Deportista',
  items: 'Implementos',
  from: 'Fecha inicial',
  to: 'Fecha final',
  status: 'Estado',
  search: 'Búsqueda',
});

export function loanStatusLabel(status) {
  return LOAN_STATUS_LABELS[status] ?? status ?? '—';
}

/** Muestra una fecha ISO 8601 (UTC) en la hora local del navegador. */
export function formatDateTime(isoString) {
  if (!isoString) return '—';
  const date = new Date(isoString);
  if (Number.isNaN(date.getTime())) return '—';
  return date.toLocaleString('es-CO', { dateStyle: 'medium', timeStyle: 'short' });
}

/** Convierte los campos de details (VALIDATION_ERROR) en etiquetas en español. */
export function invalidFieldLabels(details) {
  if (!details || typeof details !== 'object') return [];
  const labels = Object.keys(details).map((field) => {
    const root = field.split('.')[0];
    return FIELD_LABELS[root] ?? root;
  });
  return [...new Set(labels)];
}

/** Texto completo de un error de la API para mostrar al usuario. */
export function errorMessage(error) {
  if (!error) return '';
  const base = error.message || 'Ocurrió un error inesperado.';
  if (error.code === 'VALIDATION_ERROR') {
    const fields = invalidFieldLabels(error.details);
    if (fields.length > 0) return `${base} Revisa: ${fields.join(', ')}.`;
  }
  return base;
}

export function shortId(id) {
  return id ? String(id).slice(0, 8) : '—';
}
