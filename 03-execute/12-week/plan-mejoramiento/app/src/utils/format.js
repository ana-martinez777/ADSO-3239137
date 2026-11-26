// Presentation helpers: Spanish labels and readable dates.

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

/** Shows an ISO 8601 (UTC) date in the browser's local time. */
export function formatDateTime(isoString) {
  if (!isoString) return '—';
  const date = new Date(isoString);
  if (Number.isNaN(date.getTime())) return '—';
  return date.toLocaleString('es-CO', { dateStyle: 'medium', timeStyle: 'short' });
}

/** Converts the details fields (VALIDATION_ERROR) into Spanish labels. */
export function invalidFieldLabels(details) {
  if (!details || typeof details !== 'object') return [];
  const labels = Object.keys(details).map((field) => {
    const root = field.split('.')[0];
    return FIELD_LABELS[root] ?? root;
  });
  return [...new Set(labels)];
}

/** Full text of an API error to show to the user. */
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
