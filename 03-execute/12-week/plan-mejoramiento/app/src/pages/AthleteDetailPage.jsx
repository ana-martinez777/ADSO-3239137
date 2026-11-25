import { Link, useParams } from 'react-router-dom';
import { athletesApi, loansApi } from '../api/sportsGearApi';
import { useApiData } from '../hooks/useApiData';
import { formatDateTime } from '../utils/format';
import { Alert, ApiErrorAlert, EmptyState, Loading, PageHeader, StatusBadge } from '../components/ui';

export default function AthleteDetailPage() {
  const { id } = useParams();
  const athlete = useApiData(() => athletesApi.find(id), [id]);
  const loans = useApiData(() => loansApi.list({ athlete_id: id }), [id], { initialData: [] });
  const overdue = useApiData(() => loansApi.overdue({ athlete_id: id }), [id], { initialData: [] });

  if (athlete.loading) return <Loading />;
  if (athlete.error) return <ApiErrorAlert error={athlete.error} />;

  return (
    <section>
      <PageHeader title={athlete.data.full_name} description={`Documento: ${athlete.data.document_number}`} actions={<Link className="btn btn-ghost" to="/athletes">Volver</Link>} />

      {overdue.data.length > 0 && (
        <Alert type="error">
          Este deportista tiene {overdue.data.length} implemento(s) vencido(s) sin devolver y no puede recibir nuevos préstamos hasta devolverlos.
        </Alert>
      )}

      <h2>Préstamos del deportista</h2>
      <ApiErrorAlert error={loans.error} />
      {loans.loading ? (
        <Loading />
      ) : loans.data.length === 0 ? (
        <EmptyState>El deportista no tiene préstamos registrados.</EmptyState>
      ) : (
        <div className="table-wrap card">
          <table>
            <thead><tr><th>Fecha del préstamo</th><th>Estado</th><th /></tr></thead>
            <tbody>
              {loans.data.map((loan) => (
                <tr key={loan.id}>
                  <td>{formatDateTime(loan.loaned_at)}</td>
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
