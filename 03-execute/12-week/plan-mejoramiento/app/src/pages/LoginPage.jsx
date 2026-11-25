import { useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';
import { homePathFor } from '../config/permissions';
import { Alert, ApiErrorAlert } from '../components/ui';

export default function LoginPage() {
  const { login, notice } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState(null);
  const [formError, setFormError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit(event) {
    event.preventDefault();
    setError(null);
    setFormError('');
    if (!username.trim() || !password) {
      setFormError('Ingresa tu nombre de usuario y tu contraseña.');
      return;
    }
    setSubmitting(true);
    try {
      const user = await login(username, password);
      navigate(location.state?.from || homePathFor(user.role), { replace: true });
    } catch (err) {
      setError(err);
      setPassword('');
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="login-screen">
      <form className="card login-card" onSubmit={handleSubmit} noValidate>
        <div className="brand brand-large">
          <span className="brand-mark" aria-hidden="true">SG</span>
          <span>Sports Gear</span>
        </div>
        <p className="muted">Préstamo de implementos deportivos del coliseo.</p>

        <Alert type="info">{notice}</Alert>
        <Alert type="error">{formError}</Alert>
        <ApiErrorAlert error={error} />

        <label>
          Nombre de usuario
          <input
            name="username"
            autoComplete="username"
            value={username}
            onChange={(event) => setUsername(event.target.value)}
          />
        </label>
        <label>
          Contraseña
          <input
            name="password"
            type="password"
            autoComplete="current-password"
            value={password}
            onChange={(event) => setPassword(event.target.value)}
          />
        </label>
        <button type="submit" className="btn btn-primary" disabled={submitting}>
          {submitting ? 'Ingresando…' : 'Iniciar sesión'}
        </button>
      </form>
    </div>
  );
}
