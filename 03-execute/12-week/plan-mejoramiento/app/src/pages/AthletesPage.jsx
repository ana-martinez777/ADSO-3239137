import { useState } from 'react';
import { Link } from 'react-router-dom';
import { athletesApi } from '../api/sportsGearApi';
import { useApiData } from '../hooks/useApiData';
import { validateAthleteForm } from '../validation/forms';
import { Alert, ApiErrorAlert, EmptyState, ErrorList, Loading, PageHeader } from '../components/ui';

const EMPTY_FORM = { full_name: '', document_number: '' };

export default function AthletesPage() {
  const [filters, setFilters] = useState({ search: '', document_number: '' });
  const [applied, setApplied] = useState(filters);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState(EMPTY_FORM);
  const [formErrors, setFormErrors] = useState([]);
  const [actionError, setActionError] = useState(null);
  const [success, setSuccess] = useState('');
  const [saving, setSaving] = useState(false);

  const athletes = useApiData(() => athletesApi.list(applied), [applied.search, applied.document_number], { initialData: [] });

  async function handleSave(event) {
    event.preventDefault();
    const errors = validateAthleteForm(form);
    setFormErrors(errors);
    setActionError(null);
    if (errors.length > 0) return;
    setSaving(true);
    try {
      const created = await athletesApi.create({ full_name: form.full_name.trim(), document_number: form.document_number.trim() });
      setSuccess(`Deportista ${created.full_name} registrado.`);
      setForm(EMPTY_FORM);
      setShowForm(false);
      athletes.reload();
    } catch (err) {
      setActionError(err);
    } finally {
      setSaving(false);
    }
  }

  return (
    <section>
      <PageHeader
        title="Deportistas"
        description="Personas que pueden recibir implementos en préstamo. No tienen cuenta en el sistema."
        actions={
          <button type="button" className="btn btn-primary" onClick={() => { setShowForm(true); setSuccess(''); setFormErrors([]); setActionError(null); }}>
            Registrar deportista
          </button>
        }
      />
      <Alert type="success">{success}</Alert>

      {showForm && (
        <form className="card form-grid" onSubmit={handleSave} noValidate>
          <h2>Nuevo deportista</h2>
          <ErrorList errors={formErrors} />
          <ApiErrorAlert error={actionError} />
          <label>
            Nombre completo
            <input value={form.full_name} maxLength={200} onChange={(e) => setForm({ ...form, full_name: e.target.value })} />
          </label>
          <label>
            Número de documento
            <input value={form.document_number} maxLength={30} onChange={(e) => setForm({ ...form, document_number: e.target.value })} />
          </label>
          <div className="form-actions">
            <button type="submit" className="btn btn-primary" disabled={saving}>{saving ? 'Guardando…' : 'Guardar'}</button>
            <button type="button" className="btn btn-ghost" onClick={() => setShowForm(false)}>Cancelar</button>
          </div>
        </form>
      )}

      <form className="card filters" onSubmit={(e) => { e.preventDefault(); setApplied(filters); }}>
        <label>
          Buscar por nombre
          <input value={filters.search} onChange={(e) => setFilters({ ...filters, search: e.target.value })} />
        </label>
        <label>
          Número de documento
          <input value={filters.document_number} onChange={(e) => setFilters({ ...filters, document_number: e.target.value })} />
        </label>
        <button type="submit" className="btn">Buscar</button>
      </form>

      <ApiErrorAlert error={athletes.error} />
      {athletes.loading ? (
        <Loading />
      ) : athletes.data.length === 0 ? (
        <EmptyState>No hay deportistas que coincidan con la búsqueda.</EmptyState>
      ) : (
        <div className="table-wrap card">
          <table>
            <thead>
              <tr><th>Nombre completo</th><th>Documento</th><th /></tr>
            </thead>
            <tbody>
              {athletes.data.map((athlete) => (
                <tr key={athlete.id}>
                  <td>{athlete.full_name}</td>
                  <td>{athlete.document_number}</td>
                  <td className="row-actions"><Link className="btn btn-small" to={`/athletes/${athlete.id}`}>Ver detalle</Link></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </section>
  );
}
