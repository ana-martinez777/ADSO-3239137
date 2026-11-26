import { useState } from 'react';
import { coachesApi } from '../api/sportsGearApi';
import { useApiData } from '../hooks/useApiData';
import { validateCoachForm } from '../validation/forms';
import { Alert, ApiErrorAlert, EmptyState, ErrorList, Loading, PageHeader } from '../components/ui';

const EMPTY_FORM = { username: '', password: '', passwordConfirmation: '' };

export default function CoachesPage() {
  const [search, setSearch] = useState('');
  const [appliedSearch, setAppliedSearch] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState(EMPTY_FORM);
  const [formErrors, setFormErrors] = useState([]);
  const [actionError, setActionError] = useState(null);
  const [success, setSuccess] = useState('');
  const [saving, setSaving] = useState(false);

  const coaches = useApiData(() => coachesApi.list({ search: appliedSearch }), [appliedSearch], { initialData: [] });

  async function handleSave(event) {
    event.preventDefault();
    const errors = validateCoachForm(form);
    setFormErrors(errors);
    setActionError(null);
    if (errors.length > 0) return;
    setSaving(true);
    try {
      // The role is not sent: the API always assigns "coach".
      const created = await coachesApi.create({ username: form.username.trim(), password: form.password });
      setSuccess(`Entrenador ${created.username} registrado.`);
      setForm(EMPTY_FORM);
      setShowForm(false);
      coaches.reload();
    } catch (err) {
      setActionError(err);
      setForm((current) => ({ ...current, password: '', passwordConfirmation: '' }));
    } finally {
      setSaving(false);
    }
  }

  return (
    <section>
      <PageHeader
        title="Entrenadores"
        description="Usuarios con rol entrenador que registran préstamos y devoluciones."
        actions={<button type="button" className="btn btn-primary" onClick={() => { setShowForm(true); setSuccess(''); setFormErrors([]); setActionError(null); }}>Registrar entrenador</button>}
      />
      <Alert type="success">{success}</Alert>

      {showForm && (
        <form className="card form-grid" onSubmit={handleSave} noValidate>
          <h2>Nuevo entrenador</h2>
          <ErrorList errors={formErrors} />
          <ApiErrorAlert error={actionError} />
          <label>
            Nombre de usuario
            <input value={form.username} maxLength={120} autoComplete="off" onChange={(e) => setForm({ ...form, username: e.target.value })} />
          </label>
          <label>
            Contraseña
            <input type="password" autoComplete="new-password" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} />
          </label>
          <label>
            Confirmar contraseña
            <input type="password" autoComplete="new-password" value={form.passwordConfirmation} onChange={(e) => setForm({ ...form, passwordConfirmation: e.target.value })} />
          </label>
          <p className="hint">El nombre de usuario se guarda en minúsculas y debe ser único.</p>
          <div className="form-actions">
            <button type="submit" className="btn btn-primary" disabled={saving}>{saving ? 'Guardando…' : 'Guardar'}</button>
            <button type="button" className="btn btn-ghost" onClick={() => setShowForm(false)}>Cancelar</button>
          </div>
        </form>
      )}

      <form className="card filters" onSubmit={(e) => { e.preventDefault(); setAppliedSearch(search); }}>
        <label>
          Buscar por nombre de usuario
          <input value={search} onChange={(e) => setSearch(e.target.value)} />
        </label>
        <button type="submit" className="btn">Buscar</button>
      </form>

      <ApiErrorAlert error={coaches.error} />
      {coaches.loading ? (
        <Loading />
      ) : coaches.data.length === 0 ? (
        <EmptyState>No hay entrenadores registrados con ese criterio.</EmptyState>
      ) : (
        <div className="table-wrap card">
          <table>
            <thead><tr><th>Nombre de usuario</th><th>Rol</th></tr></thead>
            <tbody>
              {coaches.data.map((coach) => (
                <tr key={coach.id}><td>{coach.username}</td><td>Entrenador</td></tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </section>
  );
}
