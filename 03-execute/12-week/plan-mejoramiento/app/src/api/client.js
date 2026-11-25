// Cliente HTTP único de la aplicación.
// Centraliza el prefijo de la API, el encabezado Authorization y la
// conversión de errores al formato uniforme { code, message, details }.

const BASE_URL = (import.meta.env.VITE_API_BASE_URL || '/api/v1').replace(/\/$/, '');

export class ApiError extends Error {
  constructor(status, code, message, details = {}) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.code = code;
    this.details = details ?? {};
  }
}

let getToken = () => null;
let handleUnauthorized = () => {};

/** Permite que la capa de sesión entregue el token y reaccione a un 401. */
export function configureClient({ tokenProvider, onUnauthorized } = {}) {
  if (tokenProvider) getToken = tokenProvider;
  if (onUnauthorized) handleUnauthorized = onUnauthorized;
}

/** Construye la cadena de consulta omitiendo los valores vacíos. */
export function buildQuery(params = {}) {
  const search = new URLSearchParams();
  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      search.append(key, String(value));
    }
  });
  const query = search.toString();
  return query ? `?${query}` : '';
}

export async function request(path, { method = 'GET', body, query } = {}) {
  const headers = { Accept: 'application/json' };
  const token = getToken();
  if (token) headers.Authorization = `Bearer ${token}`;
  if (body !== undefined) headers['Content-Type'] = 'application/json';

  let response;
  try {
    response = await fetch(`${BASE_URL}${path}${buildQuery(query)}`, {
      method,
      headers,
      body: body !== undefined ? JSON.stringify(body) : undefined,
    });
  } catch {
    throw new ApiError(0, 'NETWORK_ERROR', 'No fue posible conectar con el servidor. Verifica que la API esté en ejecución.');
  }

  if (response.status === 204) return null;

  let payload = null;
  try {
    payload = await response.json();
  } catch {
    payload = null;
  }

  if (!response.ok) {
    // Solo se muestra el mensaje del servidor cuando viene con el formato
    // uniforme del contrato (tiene "code"). Así nunca se muestran textos
    // técnicos, como errores SQL, si la API falla de forma no controlada.
    const isContractError = typeof payload?.code === 'string';
    const error = new ApiError(
      response.status,
      isContractError ? payload.code : 'UNEXPECTED_ERROR',
      isContractError && payload.message ? payload.message : fallbackMessage(response.status),
      isContractError ? payload.details : {},
    );
    // Un 401 con sesión activa significa token vencido o inválido.
    if (response.status === 401 && token) handleUnauthorized(error);
    throw error;
  }

  return payload;
}

function fallbackMessage(status) {
  if (status >= 500) return `El servidor no respondió correctamente (HTTP ${status}). Intenta nuevamente más tarde.`;
  return `No fue posible completar la operación (HTTP ${status}).`;
}
