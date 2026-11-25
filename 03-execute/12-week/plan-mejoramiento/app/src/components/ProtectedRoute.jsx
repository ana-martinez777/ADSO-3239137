import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';
import { hasRole } from '../config/permissions';

/** Exige sesión iniciada y, si se indican, uno de los roles permitidos. */
export default function ProtectedRoute({ roles, children }) {
  const { user } = useAuth();
  const location = useLocation();

  if (!user) return <Navigate to="/login" replace state={{ from: location.pathname }} />;

  if (roles && !hasRole(user, roles)) {
    return (
      <section className="card forbidden">
        <h1>Acceso no permitido</h1>
        <p>Tu rol no tiene permiso para usar esta sección.</p>
      </section>
    );
  }

  return children;
}
