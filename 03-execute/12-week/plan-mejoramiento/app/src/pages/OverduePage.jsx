import { useState } from 'react';
import { Link } from 'react-router-dom';
import { athletesApi, loansApi } from '../api/sportsGearApi';
import { useApiData } from '../hooks/useApiData';
import { formatDateTime } from '../utils/format';
import { ApiErrorAlert, EmptyState, Loading, PageHeader } from '../components/ui';

export default function OverduePage() {
  const [athleteId, setAthleteId] = useState('');
  const athletes = useApiData(() => athletesApi.list(), [], { initialData: [] });
  const overdue = useApiData(() => loansApi.overdue({ athlete_id: athleteId }), [athleteId], { initialData: [] });

  return (
    <section>
      <PageHeader
        title="Préstamos vencidos"
        description="Implementos cuyo plazo de 48 horas ya se cumplió y siguen sin devolverse. El deportista no puede recibir nuevos préstamos hasta devolverlos."
      />

      <div className="card filters">
        <label>
          Deportista
          <select value={athleteId} onChange={(e) => setAthleteId(e.target.value)}>
            <option value="">Todos</option>
            {athletes.data.map((a) => <option key={a.id} value={a.id}>{a.full_name}</option>)}
          </select>
        </label>
      </div>

      <ApiErrorAlert error={overdue.error || athletes.error} />
      {overdue.loading ? (
        <Loading />
      ) : overdue.data.length === 0 ? (
        <EmptyState>No hay implementos vencidos.</EmptyState>
      ) : (
        <div className="table-wrap card">
          <table>
            <thead>
              <tr><th>Deportista</th><th>Implemento</th><th>Categoría</th><th>Venció</th><th className="num">Días de retraso</th><th /></tr>
            </thead>
            <tbody>
              {overdue.data.map((row) => (
                <tr key={row.item_id}>
                  <td><Link to={`/athletes/${row.athlete_id}`}>{row.athlete_name}</Link></td>
                  <td><strong>{row.equipment_reference}</strong></td>
                  <td>{row.category_name}</td>
                  <td>{formatDateTime(row.due_at)}</td>
                  <td className="num text-danger">{row.days_late}</td>
                  <td className="row-actions"><Link className="btn btn-small" to={`/loans/${row.loan_id}`}>Ver préstamo</Link></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </section>
  );
}
