import { describe, expect, it, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter } from 'react-router-dom';
import App from '../App';
import { AuthProvider } from '../auth/AuthContext';
import { jsonResponse } from '../test/setup';

function renderApp() {
  return render(
    <MemoryRouter initialEntries={['/login']}>
      <AuthProvider>
        <App />
      </AuthProvider>
    </MemoryRouter>,
  );
}

describe('inicio de sesión (HU-01)', () => {
  it('pide los datos obligatorios sin llamar a la API', async () => {
    const fetchMock = vi.spyOn(globalThis, 'fetch');
    renderApp();
    await userEvent.click(screen.getByRole('button', { name: 'Iniciar sesión' }));

    expect(screen.getByText('Ingresa tu nombre de usuario y tu contraseña.')).toBeInTheDocument();
    expect(fetchMock).not.toHaveBeenCalled();
  });

  it('muestra en español el error de credenciales incorrectas', async () => {
    vi.spyOn(globalThis, 'fetch').mockResolvedValue(
      jsonResponse(401, { code: 'INVALID_CREDENTIALS', message: 'Las credenciales de acceso son incorrectas.', details: {} }),
    );
    renderApp();
    await userEvent.type(screen.getByLabelText('Nombre de usuario'), 'admin');
    await userEvent.type(screen.getByLabelText('Contraseña'), 'incorrecta');
    await userEvent.click(screen.getByRole('button', { name: 'Iniciar sesión' }));

    expect(await screen.findByRole('alert')).toHaveTextContent('Las credenciales de acceso son incorrectas.');
    expect(screen.getByLabelText('Contraseña')).toHaveValue('');
  });

  it('entra con las opciones del entrenador después de un inicio de sesión correcto', async () => {
    vi.spyOn(globalThis, 'fetch').mockImplementation(async (url) => {
      if (String(url).endsWith('/auth/login')) {
        return jsonResponse(200, {
          data: { access_token: 'jwt', token_type: 'Bearer', expires_in: 28800, user: { id: 'c1', username: 'entrenador01', role: 'coach' } },
        });
      }
      return jsonResponse(200, { data: [] });
    });
    renderApp();
    await userEvent.type(screen.getByLabelText('Nombre de usuario'), 'entrenador01');
    await userEvent.type(screen.getByLabelText('Contraseña'), 'secreta');
    await userEvent.click(screen.getByRole('button', { name: 'Iniciar sesión' }));

    expect(await screen.findByRole('heading', { name: 'Registrar préstamo' })).toBeInTheDocument();
    expect(screen.queryByRole('link', { name: 'Entrenadores' })).not.toBeInTheDocument();
    expect(screen.getByRole('link', { name: 'Vencidos' })).toBeInTheDocument();
  });
});
