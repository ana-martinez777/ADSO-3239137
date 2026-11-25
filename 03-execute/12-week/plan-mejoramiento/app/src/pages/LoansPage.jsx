import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';
import { can } from '../config/permissions';
import { loansApi } from '../api/sportsGearApi';
import { useApiData } from '../hooks/useApiData';
import { usePeopleNames } from '../hooks/usePeopleNames';
import { validateDateRange } from '../validation/forms';
import { formatDateTime } from '../utils/format';
import { Alert, ApiErrorAlert, EmptyState, Loading, PageHeader, StatusBadge } from '../components/ui';

const EMPTY_FILTERS = { from: '', to: '', status: '', athlete_id: '', user_id: '' };

export default function LoansPage() {
  const { user } = useAuth();
  const isAdmin = can.manageCoaches(user.role);
  const people = usePeopleNames(user);
  const [filters, setFilters] = useState(EMPTY_FILTERS);
  const [applied, setApplied] = useState(EMPTY_FILTERS);
  const [filterError, setFilterError] = useState('');

  const loans = useApiData(() => loansApi.list(applied), [JSON.stringify(applied)], { initialData: [] });

  function applyFilters(event) {
    event.preventDefault();
    const error = validateDateRange(filters.from, filters.to);
    setFilterError(error ?? '');
    if (!error) setApplied(filters);
  }

  return (
    <section>
      <PageHeader
        title="Préstamos"
        description="Historial de préstamos con su estado calculado a partir de vencimientos y devoluciones."
        actions={can.registerLoan(user.role) && <Link className="btn btn-primary" to="/loans/new">Nuevo préstamo</Link>}
      />

      <form className="card filters" onSubmit={applyFilters}>
        <label>
          Desde
          <input type="date" value={filters.from} onChange={(e) => setFilters({ ...filters, from: e.target.value })} />
        </label>
        <label>
          Hasta
          <input type="date" value={filters.to} onChange={(e) => setFilters({ ...filters, to: e.target.value })} />
        </label>
        <label>
          Estado
          <select value={filters.status} onChange={(e) => setFilters({ ...filters, status: e.target.value })}>
            <option value="">Todos</option>
            <option value="pending">Pendiente</option>
            <option value="overdue">Vencido</option>
            <option value="returned">Devuelto</option>
          </select>
        </label>
        <label>
          Deportista
          <select value={filters.athlete_id} onChange={(e) => setFilters({ ...filters, athlete_id: e.target.value })}>
            <option value="">Todos</option>
            {people.athletes.map((a) => <option key={a.id} value={a.id}>{a.full_name}</option>)}
          </select>
        </label>
        {isAdmin && (
          <label>
            Entrenador
            <select value={filters.user_id} onChange={(e) => setFilters({ ...filters, user_id: e.target.value })}>
              <option value="">Todos</option>
              {people.coaches.map((c) => <option key={c.id} value={c.id}>{c.username}</option>)}
            </select>
          </label>
        )}
        {!isAdmin && (
          <label className="checkbox">
            <input
              type="checkbox"
              checked={filters.user_id === user.id}
              onChange={(e) => setFilters({ ...filters, user_id: e.target.checked ? user.id : '' })}
            />
            Solo mis préstamos
          </label>
        )}
        <button type="submit" className="btn">Filtrar</button>
        <button type="button" className="btn btn-ghost" onClick={() => { setFilters(EMPTY_FILTERS); setApplied(EMPTY_FILTERS); setFilterError(''); }}>Limpiar</button>
      </form>

      <Alert type="error">{filterError}</Alert>
      <ApiErrorAlert error={loans.error || people.error} />

      {loans.loading ? (
        <Loading />
      ) : loans.data.length === 0 ? (
        <EmptyState>No hay préstamos con los filtros seleccionados.</EmptyState>
      ) : (
        <div className="table-wrap card">
          <table>
            <thead>
              <tr><th>Fecha del préstamo</th><th>Deportista</th><th>Registrado por</th><th>Estado</th><th /></tr>
            </thead>
            <tbody>
              {loans.data.map((loan) => (
                <tr key={loan.id}>
                  <td>{formatDateTime(loan.loaned_at)}</td>
                  <td>{people.athleteName(loan.athlete_id)}</td>
                  <td>{people.coachName(loan.user_id)}</td>
                  <td><StatusBadge status={loan.status} /></td>
                  <td className="row-actions"><Link className="btn btn-small" to={`/loans/${loan.id}`}>Ver detalle</Link></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </section>
  );
}
