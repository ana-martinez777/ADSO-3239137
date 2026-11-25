import { describe, expect, it } from 'vitest';
import { render, screen } from '@testing-library/react';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { AuthProvider } from '../auth/AuthContext';
import { saveSession } from '../auth/tokenStorage';
import ProtectedRoute from './ProtectedRoute';

function renderAt(path, roles) {
  return render(
    <MemoryRouter initialEntries={[path]}>
      <AuthProvider>
        <Routes>
          <Route path="/login" element={<p>Pantalla de inicio de sesión</p>} />
          <Route path="/coaches" element={<ProtectedRoute roles={roles}><p>Contenido privado</p></ProtectedRoute>} />
        </Routes>
      </AuthProvider>
    </MemoryRouter>,
  );
}

const login = (role) => saveSession({ access_token: 't', expires_in: 3600, user: { id: '1', username: 'u', role } });

describe('ProtectedRoute', () => {
  it('redirige al inicio de sesión si no hay sesión', () => {
    renderAt('/coaches', ['admin']);
    expect(screen.getByText('Pantalla de inicio de sesión')).toBeInTheDocument();
  });

  it('bloquea a un rol no autorizado', () => {
    login('coach');
    renderAt('/coaches', ['admin']);
    expect(screen.getByText('Acceso no permitido')).toBeInTheDocument();
    expect(screen.queryByText('Contenido privado')).not.toBeInTheDocument();
  });

  it('muestra el contenido al rol autorizado', () => {
    login('admin');
    renderAt('/coaches', ['admin']);
    expect(screen.getByText('Contenido privado')).toBeInTheDocument();
  });
});
