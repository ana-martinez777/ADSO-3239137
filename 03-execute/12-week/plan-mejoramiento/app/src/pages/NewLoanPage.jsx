import { useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { athletesApi, equipmentApi, loansApi } from '../api/sportsGearApi';
import { useApiData } from '../hooks/useApiData';
import { MAX_PENDING_UNITS, validateLoanDraft } from '../validation/forms';
import { ApiErrorAlert, ErrorList, Loading, PageHeader } from '../components/ui';

const newRow = () => ({ key: crypto.randomUUID?.() ?? String(Math.random()), equipmentId: '', quantity: 1 });

export default function NewLoanPage() {
  const navigate = useNavigate();
  const [athleteId, setAthleteId] = useState('');
  const [rows, setRows] = useState([newRow()]);
  const [errors, setErrors] = useState([]);
  const [apiError, setApiError] = useState(null);
  const [saving, setSaving] = useState(false);

  const athletes = useApiData(() => athletesApi.list(), [], { initialData: [] });
  // Solo se ofrecen implementos activos; la API vuelve a validar al registrar.
  const equipment = useApiData(() => equipmentApi.list({ status: 'active' }), [], { initialData: [] });

  const equipmentById = useMemo(() => new Map(equipment.data.map((item) => [item.id, item])), [equipment.data]);
  const totalUnits = rows.reduce((sum, row) => sum + (row.equipmentId ? Number(row.quantity) || 0 : 0), 0);

  function updateRow(key, changes) {
    setRows((current) => current.map((row) => (row.key === key ? { ...row, ...changes } : row)));
  }

  async function handleSubmit(event) {
    event.preventDefault();
    setApiError(null);
    const validation = validateLoanDraft({ athleteId, items: rows }, equipmentById);
    setErrors(validation);
    if (validation.length > 0) return;

    setSaving(true);
    try {
      const loan = await loansApi.create(buildLoanPayload(athleteId, rows));
      navigate(`/loans/${loan.id}`, { state: { created: true } });
    } catch (err) {
      setApiError(err);
      // La disponibilidad pudo cambiar por otro entrenador: se actualiza.
      if (err.code === 'EQUIPMENT_NOT_AVAILABLE' || err.code === 'EQUIPMENT_INACTIVE') equipment.reload();
    } finally {
      setSaving(false);
    }
  }

  if (athletes.loading || equipment.loading) return <Loading />;

  return (
    <section>
      <PageHeader
        title="Registrar préstamo"
        description={`Máximo ${MAX_PENDING_UNITS} unidades pendientes por deportista. Plazo de devolución: 48 horas.`}
      />
      <ApiErrorAlert error={athletes.error || equipment.error} />

      <form className="card form-grid" onSubmit={handleSubmit} noValidate>
        <ErrorList errors={errors} />
        <ApiErrorAlert error={apiError} />

        <label>
          Deportista
          <select value={athleteId} onChange={(e) => setAthleteId(e.target.value)}>
            <option value="">Selecciona un deportista…</option>
            {athletes.data.map((a) => (
              <option key={a.id} value={a.id}>{a.full_name} — {a.document_number}</option>
            ))}
          </select>
        </label>

        <fieldset className="loan-items">
          <legend>Implementos</legend>
          {rows.map((row, index) => {
            const selected = equipmentById.get(row.equipmentId);
            const usedIds = rows.filter((r) => r.key !== row.key).map((r) => r.equipmentId);
            return (
              <div className="loan-row" key={row.key}>
                <label>
                  Implemento {index + 1}
                  <select value={row.equipmentId} onChange={(e) => updateRow(row.key, { equipmentId: e.target.value })}>
                    <option value="">Selecciona…</option>
                    {equipment.data.map((item) => (
                      <option key={item.id} value={item.id} disabled={item.available_units === 0 || usedIds.includes(item.id)}>
                        {item.reference} ({item.category?.name}) — {item.available_units} disponible(s)
                      </option>
                    ))}
                  </select>
                </label>
                <label className="qty">
                  Cantidad
                  <input
                    type="number"
                    min="1"
                    max={selected?.available_units ?? MAX_PENDING_UNITS}
                    value={row.quantity}
                    onChange={(e) => updateRow(row.key, { quantity: e.target.value })}
                  />
                </label>
                {rows.length > 1 && (
                  <button type="button" className="btn btn-small btn-ghost" onClick={() => setRows(rows.filter((r) => r.key !== row.key))}>
                    Quitar
                  </button>
                )}
              </div>
            );
          })}
          <button type="button" className="btn btn-small" onClick={() => setRows([...rows, newRow()])} disabled={rows.length >= MAX_PENDING_UNITS}>
            Agregar implemento
          </button>
        </fieldset>

        <p className={totalUnits > MAX_PENDING_UNITS ? 'hint text-danger' : 'hint'}>
          Unidades en este préstamo: {totalUnits}. La API también suma las unidades que el deportista ya tiene pendientes.
        </p>

        <div className="form-actions">
          <button type="submit" className="btn btn-primary" disabled={saving}>{saving ? 'Registrando…' : 'Registrar préstamo'}</button>
        </div>
      </form>
    </section>
  );
}

// Construye el cuerpo de POST /loans con los campos definidos en el contrato.
export function buildLoanPayload(athleteId, rows) {
  return {
    athlete_id: athleteId,
    items: rows
      .filter((row) => row.equipmentId)
      .map((row) => ({ equipment_id: row.equipmentId, quantity: Number(row.quantity) })),
  };
}
