import { useState } from 'react';
import { Link, useLocation, useParams } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';
import { can } from '../config/permissions';
import { loansApi } from '../api/sportsGearApi';
import { useApiData } from '../hooks/useApiData';
import { usePeopleNames } from '../hooks/usePeopleNames';
import { formatDateTime } from '../utils/format';
import { Alert, ApiErrorAlert, Loading, PageHeader, StatusBadge } from '../components/ui';

export default function LoanDetailPage() {
  const { id } = useParams();
  const location = useLocation();
  const { user } = useAuth();
  const people = usePeopleNames(user);
  const canReturn = can.returnItem(user.role);

  const loan = useApiData(() => loansApi.find(id), [id]);
  const [success, setSuccess] = useState(location.state?.created ? 'Préstamo registrado correctamente. Plazo de devolución: 48 horas.' : '');
  const [actionError, setActionError] = useState(null);
  const [returningId, setReturningId] = useState(null);

  async function handleReturn(item) {
    setActionError(null);
    setSuccess('');
    setReturningId(item.id);
    try {
      await loansApi.returnItem(id, item.id);
      setSuccess(`Devolución registrada: ${item.quantity} unidad(es) de ${item.equipment_reference}.`);
      await loan.reload();
    } catch (err) {
      setActionError(err);
    } finally {
      setReturningId(null);
    }
  }

  if (loan.loading && !loan.data) return <Loading />;
  if (loan.error && !loan.data) return <ApiErrorAlert error={loan.error} />;

  const data = loan.data;
  const pendingCount = data.items.filter((item) => !item.returned_at).length;

  return (
    <section>
      <PageHeader
        title="Detalle del préstamo"
        description={`Registrado el ${formatDateTime(data.loaned_at)}`}
        actions={<Link className="btn btn-ghost" to="/loans">Volver a préstamos</Link>}
      />
      <Alert type="success">{success}</Alert>
      <ApiErrorAlert error={actionError} />

      <div className="summary card">
        <div><span className="muted">Deportista</span><Link to={`/athletes/${data.athlete_id}`}>{people.athleteName(data.athlete_id)}</Link></div>
        <div><span className="muted">Registrado por</span><strong>{people.coachName(data.user_id)}</strong></div>
        <div><span className="muted">Estado</span><StatusBadge status={data.status} /></div>
        <div><span className="muted">Pendientes de devolución</span><strong>{pendingCount} de {data.items.length}</strong></div>
      </div>

      <div className="table-wrap card">
        <table>
          <thead>
            <tr>
              <th>Implemento</th>
              <th>Categoría</th>
              <th className="num">Cantidad</th>
              <th>Vence</th>
              <th>Devuelto</th>
              <th>Estado</th>
              {canReturn && <th>Acción</th>}
            </tr>
          </thead>
          <tbody>
            {data.items.map((item) => (
              <tr key={item.id}>
                <td><strong>{item.equipment_reference}</strong></td>
                <td>{item.category_name}</td>
                <td className="num">{item.quantity}</td>
                <td>{formatDateTime(item.due_at)}</td>
                <td>{formatDateTime(item.returned_at)}</td>
                <td><StatusBadge status={item.status} /></td>
                {canReturn && (
                  <td>
                    {!item.returned_at && (
                      <button type="button" className="btn btn-small btn-primary" disabled={returningId !== null} onClick={() => handleReturn(item)}>
                        {returningId === item.id ? 'Registrando…' : 'Registrar devolución'}
                      </button>
                    )}
                  </td>
                )}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      <p className="hint">La referencia y la categoría se muestran tal como estaban al registrar el préstamo. La devolución se hace por detalle e incluye todas sus unidades.</p>
    </section>
  );
}
