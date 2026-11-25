import { useState } from 'react';
import { reportsApi } from '../api/sportsGearApi';
import { useApiData } from '../hooks/useApiData';
import { validateDateRange } from '../validation/forms';
import { Alert, ApiErrorAlert, EmptyState, Loading, PageHeader } from '../components/ui';

export default function MostRequestedReportPage() {
  const [range, setRange] = useState({ from: '', to: '' });
  const [applied, setApplied] = useState({ from: '', to: '' });
  const [rangeError, setRangeError] = useState('');

  const report = useApiData(() => reportsApi.mostRequested(applied), [applied.from, applied.to], { initialData: [] });
  const max = Math.max(1, ...report.data.map((row) => row.total_requested));

  function applyRange(event) {
    event.preventDefault();
    const error = validateDateRange(range.from, range.to);
    setRangeError(error ?? '');
    if (!error) setApplied(range);
  }

  return (
    <section>
      <PageHeader
        title="Implementos más solicitados"
        description="Unidades solicitadas por implemento según los préstamos registrados. Sin fechas se consulta todo el historial."
      />

      <form className="card filters" onSubmit={applyRange}>
        <label>
          Desde
          <input type="date" value={range.from} onChange={(e) => setRange({ ...range, from: e.target.value })} />
        </label>
        <label>
          Hasta
          <input type="date" value={range.to} onChange={(e) => setRange({ ...range, to: e.target.value })} />
        </label>
        <button type="submit" className="btn">Consultar</button>
        <button type="button" className="btn btn-ghost" onClick={() => { setRange({ from: '', to: '' }); setApplied({ from: '', to: '' }); setRangeError(''); }}>
          Todo el historial
        </button>
      </form>

      <Alert type="error">{rangeError}</Alert>
      <ApiErrorAlert error={report.error} />

      {report.loading ? (
        <Loading />
      ) : report.data.length === 0 ? (
        <EmptyState>No hay préstamos registrados en el período consultado.</EmptyState>
      ) : (
        <div className="table-wrap card">
          <table>
            <thead>
              <tr><th className="num">#</th><th>Implemento</th><th>Categoría</th><th className="num">Unidades solicitadas</th><th aria-hidden="true" /></tr>
            </thead>
            <tbody>
              {report.data.map((row, index) => (
                <tr key={`${row.equipment_reference}-${row.category_name}`}>
                  <td className="num">{index + 1}</td>
                  <td><strong>{row.equipment_reference}</strong></td>
                  <td>{row.category_name}</td>
                  <td className="num">{row.total_requested}</td>
                  <td className="bar-cell" aria-hidden="true">
                    <span className="bar" style={{ width: `${(row.total_requested / max) * 100}%` }} />
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </section>
  );
}
