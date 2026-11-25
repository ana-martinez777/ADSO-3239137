import { NavLink, Outlet } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';
import { navItemsFor, ROLE_LABELS } from '../config/permissions';

export default function Layout() {
  const { user, logout } = useAuth();
  const items = navItemsFor(user.role);

  return (
    <div className="shell">
      <aside className="sidebar">
        <div className="brand">
          <span className="brand-mark" aria-hidden="true">SG</span>
          <span>Sports Gear</span>
        </div>
        <nav aria-label="Navegación principal">
          {items.map((item) => (
            <NavLink key={item.path} to={item.path} end={item.path === '/loans'}>
              {item.label}
            </NavLink>
          ))}
        </nav>
        <div className="session">
          <div>
            <strong>{user.username}</strong>
            <span className="muted">{ROLE_LABELS[user.role] ?? user.role}</span>
          </div>
          <button type="button" className="btn btn-ghost" onClick={() => logout()}>
            Cerrar sesión
          </button>
        </div>
      </aside>
      <main className="content">
        <Outlet />
      </main>
    </div>
  );
}
