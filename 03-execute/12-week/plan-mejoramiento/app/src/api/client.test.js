import { beforeEach, describe, expect, it, vi } from 'vitest';
import { ApiError, buildQuery, configureClient, request } from './client';
import { jsonResponse } from '../test/setup';

describe('cliente HTTP', () => {
  let onUnauthorized;

  beforeEach(() => {
    onUnauthorized = vi.fn();
    configureClient({ tokenProvider: () => 'token-123', onUnauthorized });
  });

  it('omite los parámetros vacíos al construir la consulta', () => {
    expect(buildQuery({ search: 'BAL', category_id: '', status: undefined, from: null })).toBe('?search=BAL');
    expect(buildQuery({})).toBe('');
  });

  it('envía el token Bearer y el prefijo /api/v1', async () => {
    const fetchMock = vi.spyOn(globalThis, 'fetch').mockResolvedValue(jsonResponse(200, { data: [] }));
    await request('/equipment', { query: { status: 'active' } });

    const [url, options] = fetchMock.mock.calls[0];
    expect(url).toBe('/api/v1/equipment?status=active');
    expect(options.headers.Authorization).toBe('Bearer token-123');
  });

  it('convierte el error uniforme de la API en ApiError', async () => {
    vi.spyOn(globalThis, 'fetch').mockResolvedValue(
      jsonResponse(409, { code: 'EQUIPMENT_NOT_AVAILABLE', message: 'No hay unidades disponibles del implemento solicitado.', details: {} }),
    );

    await expect(request('/loans', { method: 'POST', body: {} })).rejects.toMatchObject({
      status: 409,
      code: 'EQUIPMENT_NOT_AVAILABLE',
      message: 'No hay unidades disponibles del implemento solicitado.',
    });
  });

  it('devuelve null en respuestas 204', async () => {
    vi.spyOn(globalThis, 'fetch').mockResolvedValue({ ok: true, status: 204, json: async () => { throw new Error('sin cuerpo'); } });
    await expect(request('/equipment/1', { method: 'DELETE' })).resolves.toBeNull();
  });

  it('avisa a la sesión cuando la API responde 401 con un token enviado', async () => {
    vi.spyOn(globalThis, 'fetch').mockResolvedValue(jsonResponse(401, { code: 'UNAUTHENTICATED', message: 'Debes iniciar sesión.', details: {} }));
    await expect(request('/athletes')).rejects.toBeInstanceOf(ApiError);
    expect(onUnauthorized).toHaveBeenCalledTimes(1);
  });

  it('no muestra textos técnicos cuando la respuesta no tiene el formato del contrato', async () => {
    vi.spyOn(globalThis, 'fetch').mockResolvedValue(
      jsonResponse(500, { message: 'SQLSTATE[HY000] [1044] Access denied ... select * from `users`' }),
    );
    const error = await request('/auth/login', { method: 'POST', body: {} }).catch((err) => err);

    expect(error.code).toBe('UNEXPECTED_ERROR');
    expect(error.message).toBe('El servidor no respondió correctamente (HTTP 500). Intenta nuevamente más tarde.');
    expect(error.message).not.toContain('SQLSTATE');
  });

  it('informa en español cuando no se puede conectar con el servidor', async () => {
    vi.spyOn(globalThis, 'fetch').mockRejectedValue(new TypeError('Failed to fetch'));
    await expect(request('/health')).rejects.toMatchObject({ code: 'NETWORK_ERROR', status: 0 });
  });
});
