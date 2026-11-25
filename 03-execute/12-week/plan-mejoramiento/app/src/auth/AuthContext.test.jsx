import { describe, expect, it, vi } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import App from '../App';
import { AuthProvider } from './AuthContext';
import { saveSession } from './tokenStorage';
import { jsonResponse } from '../test/setup';

function renderAt(path) {
  return render(
    <MemoryRouter initialEntries={[path]}>
      <AuthProvider>
        <App />
      </AuthProvider>
    </MemoryRouter>,
  );
}

describe('sesión al recargar la aplicación', () => {
  it('las primeras peticiones de una pantalla incluyen el token guardado', async () => {
    saveSession({ access_token: 'jwt-guardado', expires_in: 3600, user: { id: 'a1', username: 'admin', role: 'admin' } });
    const fetchMock = vi.spyOn(globalThis, 'fetch').mockResolvedValue(jsonResponse(200, { data: [] }));

    renderAt('/equipment');

    await waitFor(() => expect(fetchMock).toHaveBeenCalled());
    fetchMock.mock.calls.forEach(([, options]) => {
      expect(options.headers.Authorization).toBe('Bearer jwt-guardado');
    });
    expect(await screen.findByText('No hay implementos que coincidan con la búsqueda.')).toBeInTheDocument();
  });

  it('cierra la sesión y lo informa cuando la API responde 401', async () => {
    saveSession({ access_token: 'jwt-vencido', expires_in: 3600, user: { id: 'a1', username: 'admin', role: 'admin' } });
    vi.spyOn(globalThis, 'fetch').mockResolvedValue(
      jsonResponse(401, { code: 'UNAUTHENTICATED', message: 'Debes iniciar sesión para realizar esta operación.', details: {} }),
    );

    renderAt('/equipment');

    expect(await screen.findByText('Tu sesión expiró. Inicia sesión nuevamente.')).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Iniciar sesión' })).toBeInTheDocument();
  });
});
