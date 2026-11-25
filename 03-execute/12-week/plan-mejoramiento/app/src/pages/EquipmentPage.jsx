import { useState } from 'react';
import { useAuth } from '../auth/AuthContext';
import { can } from '../config/permissions';
import { categoriesApi, equipmentApi } from '../api/sportsGearApi';
import { useApiData } from '../hooks/useApiData';
import { validateEquipmentForm } from '../validation/forms';
import { ActiveBadge, Alert, ApiErrorAlert, EmptyState, ErrorList, Loading, PageHeader } from '../components/ui';

const EMPTY_FORM = { reference: '', category_id: '', total_units: 1 };

export default function EquipmentPage() {
  const { user } = useAuth();
  const isAdmin = can.manageEquipment(user.role);

  const [filters, setFilters] = useState({ search: '', category_id: '', status: 'active' });
  const [applied, setApplied] = useState(filters);
  const [editing, setEditing] = useState(null); // null | 'new' | equipment
  const [form, setForm] = useState(EMPTY_FORM);
  const [formErrors, setFormErrors] = useState([]);
  const [actionError, setActionError] = useState(null);
  const [success, setSuccess] = useState('');
  const [confirmId, setConfirmId] = useState(null);
  const [saving, setSaving] = useState(false);

  const categories = useApiData(() => categoriesApi.list(), [], { initialData: [] });
  const equipment = useApiData(
    () => equipmentApi.list({ ...applied, status: isAdmin ? applied.status : undefined }),
    [applied.search, applied.category_id, applied.status],
    { initialData: [] },
  );

  function openForm(item) {
    setSuccess('');
    setActionError(null);
    setFormErrors([]);
    if (item === 'new') {
      setForm(EMPTY_FORM);
    } else {
      setForm({ reference: item.reference, category_id: item.category?.id ?? item.category_id, total_units: item.total_units });
    }
    setEditing(item);
  }

  async function handleSave(event) {
    event.preventDefault();
    const errors = validateEquipmentForm(form);
    setFormErrors(errors);
    setActionError(null);
    if (errors.length > 0) return;

    const payload = { reference: form.reference.trim(), category_id: form.category_id, total_units: Number(form.total_units) };
    setSaving(true);
    try {
      if (editing === 'new') {
        await equipmentApi.create(payload);
        setSuccess(`Implemento ${payload.reference} registrado.`);
      } else {
        await equipmentApi.update(editing.id, payload);
        setSuccess(`Implemento ${payload.reference} actualizado.`);
      }
      setEditing(null);
      equipment.reload();
    } catch (err) {
      setActionError(err);
    } finally {
      setSaving(false);
    }
  }

  async function handleDeactivate(item) {
    setActionError(null);
    setSuccess('');
    try {
      await equipmentApi.deactivate(item.id);
      setSuccess(`Implemento ${item.reference} dado de baja. Su historial de préstamos se conserva.`);
      setConfirmId(null);
      equipment.reload();
    } catch (err) {
      setActionError(err);
    }
  }

  function applyFilters(event) {
    event.preventDefault();
    setApplied(filters);
  }

  return (
    <section>
      <PageHeader
        title="Catálogo de implementos"
        description="Consulta la referencia, la categoría y la disponibilidad de cada implemento."
        actions={isAdmin && <button type="button" className="btn btn-primary" onClick={() => openForm('new')}>Registrar implemento</button>}
      />

      <Alert type="success">{success}</Alert>
      {!editing && <ApiErrorAlert error={actionError} />}

      {editing && (
        <form className="card form-grid" onSubmit={handleSave} noValidate>
          <h2>{editing === 'new' ? 'Nuevo implemento' : `Editar ${editing.reference}`}</h2>
          <ErrorList errors={formErrors} />
          <ApiErrorAlert error={actionError} />
          <label>
            Referencia
            <input value={form.reference} onChange={(e) => setForm({ ...form, reference: e.target.value })} maxLength={120} />
          </label>
          <label>
            Categoría
            <select value={form.category_id} onChange={(e) => setForm({ ...form, category_id: e.target.value })}>
              <option value="">Selecciona…</option>
              {categories.data.map((category) => (
                <option key={category.id} value={category.id}>{category.name}</option>
              ))}
            </select>
          </label>
          <label>
            Total de unidades
            <input type="number" min="1" step="1" value={form.total_units} onChange={(e) => setForm({ ...form, total_units: e.target.value })} />
          </label>
          <p className="hint">
            Las referencias quedan reservadas de forma permanente: una referencia usada antes no puede volver a asignarse.
            {editing !== 'new' && ` Unidades prestadas actualmente: ${editing.total_units - editing.available_units}; el total no puede ser menor.`}
          </p>
          <div className="form-actions">
            <button type="submit" className="btn btn-primary" disabled={saving}>{saving ? 'Guardando…' : 'Guardar'}</button>
            <button type="button" className="btn btn-ghost" onClick={() => setEditing(null)}>Cancelar</button>
          </div>
        </form>
      )}

      <form className="card filters" onSubmit={applyFilters}>
        <label>
          Buscar por referencia
          <input value={filters.search} onChange={(e) => setFilters({ ...filters, search: e.target.value })} placeholder="Ej.: BAL-001" />
        </label>
        <label>
          Categoría
          <select value={filters.category_id} onChange={(e) => setFilters({ ...filters, category_id: e.target.value })}>
            <option value="">Todas</option>
            {categories.data.map((category) => (
              <option key={category.id} value={category.id}>{category.name}</option>
            ))}
          </select>
        </label>
        {can.viewInactiveEquipment(user.role) && (
          <label>
            Estado
            <select value={filters.status} onChange={(e) => setFilters({ ...filters, status: e.target.value })}>
              <option value="active">Activos</option>
              <option value="inactive">Inactivos</option>
              <option value="all">Todos</option>
            </select>
          </label>
        )}
        <button type="submit" className="btn">Buscar</button>
      </form>

      <ApiErrorAlert error={equipment.error || categories.error} />
      {equipment.loading ? (
        <Loading />
      ) : equipment.data.length === 0 ? (
        <EmptyState>No hay implementos que coincidan con la búsqueda.</EmptyState>
      ) : (
        <div className="table-wrap card">
          <table>
            <thead>
              <tr>
                <th>Referencia</th>
                <th>Categoría</th>
                <th className="num">Total</th>
                <th className="num">Disponibles</th>
                <th>Estado</th>
                {isAdmin && <th>Acciones</th>}
              </tr>
            </thead>
            <tbody>
              {equipment.data.map((item) => (
                <tr key={item.id}>
                  <td><strong>{item.reference}</strong></td>
                  <td>{item.category?.name ?? '—'}</td>
                  <td className="num">{item.total_units}</td>
                  <td className="num">
                    <span className={item.available_units === 0 ? 'text-danger' : ''}>{item.available_units}</span>
                  </td>
                  <td><ActiveBadge deletedAt={item.deleted_at} /></td>
                  {isAdmin && (
                    <td className="row-actions">
                      {!item.deleted_at && confirmId !== item.id && (
                        <>
                          <button type="button" className="btn btn-small" onClick={() => openForm(item)}>Editar</button>
                          <button type="button" className="btn btn-small btn-danger" onClick={() => setConfirmId(item.id)}>Dar de baja</button>
                        </>
                      )}
                      {confirmId === item.id && (
                        <>
                          <span>¿Confirmas la baja?</span>
                          <button type="button" className="btn btn-small btn-danger" onClick={() => handleDeactivate(item)}>Sí, dar de baja</button>
                          <button type="button" className="btn btn-small btn-ghost" onClick={() => setConfirmId(null)}>No</button>
                        </>
                      )}
                    </td>
                  )}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </section>
  );
}
