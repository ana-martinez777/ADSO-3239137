// Componentes visuales reutilizables sin lógica de negocio.
import { errorMessage, loanStatusLabel } from '../utils/format';

export function PageHeader({ title, description, actions }) {
  return (
    <header className="page-header">
      <div>
        <h1>{title}</h1>
        {description && <p className="muted">{description}</p>}
      </div>
      {actions && <div className="page-actions">{actions}</div>}
    </header>
  );
}

export function Alert({ type = 'error', children }) {
  if (!children) return null;
  return (
    <div className={`alert alert-${type}`} role={type === 'error' ? 'alert' : 'status'}>
      {children}
    </div>
  );
}

export function ApiErrorAlert({ error }) {
  if (!error) return null;
  return <Alert type="error">{errorMessage(error)}</Alert>;
}

export function ErrorList({ errors }) {
  if (!errors || errors.length === 0) return null;
  return (
    <div className="alert alert-error" role="alert">
      <ul>
        {errors.map((message) => (
          <li key={message}>{message}</li>
        ))}
      </ul>
    </div>
  );
}

export function Loading({ text = 'Cargando…' }) {
  return <p className="muted loading">{text}</p>;
}

export function EmptyState({ children }) {
  return <p className="empty">{children}</p>;
}

export function StatusBadge({ status }) {
  return <span className={`badge badge-${status}`}>{loanStatusLabel(status)}</span>;
}

export function ActiveBadge({ deletedAt }) {
  return deletedAt ? <span className="badge badge-inactive">Inactivo</span> : <span className="badge badge-active">Activo</span>;
}
