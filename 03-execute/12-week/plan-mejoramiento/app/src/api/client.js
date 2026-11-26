// Single HTTP client for the application.
// Centralizes the API prefix, the Authorization header and the
// conversion of errors to the uniform { code, message, details } format.

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

/** Lets the session layer provide the token and react to a 401. */
export function configureClient({ tokenProvider, onUnauthorized } = {}) {
  if (tokenProvider) getToken = tokenProvider;
  if (onUnauthorized) handleUnauthorized = onUnauthorized;
}

/** Builds the query string, skipping empty values. */
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
    // The server message is shown only when it uses the contract's
    // uniform format (has "code"). This way technical text, such as
    // SQL errors, is never shown if the API fails unexpectedly.
    const isContractError = typeof payload?.code === 'string';
    const error = new ApiError(
      response.status,
      isContractError ? payload.code : 'UNEXPECTED_ERROR',
      isContractError && payload.message ? payload.message : fallbackMessage(response.status),
      isContractError ? payload.details : {},
    );
    // A 401 with an active session means an expired or invalid token.
    if (response.status === 401 && token) handleUnauthorized(error);
    throw error;
  }

  return payload;
}

function fallbackMessage(status) {
  if (status >= 500) return `El servidor no respondió correctamente (HTTP ${status}). Intenta nuevamente más tarde.`;
  return `No fue posible completar la operación (HTTP ${status}).`;
}
