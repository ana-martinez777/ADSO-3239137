import { describe, expect, it } from 'vitest';
import { validateCoachForm, validateDateRange, validateEquipmentForm, validateLoanDraft } from './forms';

const equipment = new Map([
  ['e1', { reference: 'BAL-001', available_units: 5 }],
  ['e2', { reference: 'RAQ-001', available_units: 1 }],
]);

describe('validación complementaria del préstamo', () => {
  it('acepta un préstamo válido de hasta tres unidades', () => {
    const draft = { athleteId: 'a1', items: [{ equipmentId: 'e1', quantity: 2 }, { equipmentId: 'e2', quantity: 1 }] };
    expect(validateLoanDraft(draft, equipment)).toEqual([]);
  });

  it('exige deportista y al menos un implemento', () => {
    const errors = validateLoanDraft({ athleteId: '', items: [{ equipmentId: '', quantity: 1 }] }, equipment);
    expect(errors).toHaveLength(2);
  });

  it('rechaza implementos repetidos', () => {
    const draft = { athleteId: 'a1', items: [{ equipmentId: 'e1', quantity: 1 }, { equipmentId: 'e1', quantity: 1 }] };
    expect(validateLoanDraft(draft, equipment)).toContain('Un mismo implemento no puede repetirse en el préstamo.');
  });

  it('rechaza más de tres unidades y cantidades mayores a las disponibles', () => {
    const draft = { athleteId: 'a1', items: [{ equipmentId: 'e1', quantity: 3 }, { equipmentId: 'e2', quantity: 2 }] };
    const errors = validateLoanDraft(draft, equipment);
    expect(errors.some((e) => e.includes('máximo 3 unidades'))).toBe(true);
    expect(errors.some((e) => e.includes('RAQ-001'))).toBe(true);
  });

  it('rechaza cantidades en cero o decimales', () => {
    const draft = { athleteId: 'a1', items: [{ equipmentId: 'e1', quantity: 0 }] };
    expect(validateLoanDraft(draft, equipment)[0]).toContain('mayor que cero');
  });
});

describe('validación de períodos', () => {
  it('permite consultar sin fechas o con ambas fechas en orden', () => {
    expect(validateDateRange('', '')).toBeNull();
    expect(validateDateRange('2026-11-01', '2026-11-30')).toBeNull();
  });

  it('exige ambas fechas y que la inicial no sea posterior', () => {
    expect(validateDateRange('2026-11-01', '')).toMatch(/fecha inicial y la fecha final/);
    expect(validateDateRange('2026-11-30', '2026-11-01')).toMatch(/posterior/);
  });
});

describe('formularios de catálogo y entrenadores', () => {
  it('valida referencia, categoría y total mínimo de una unidad', () => {
    expect(validateEquipmentForm({ reference: ' ', category_id: '', total_units: 0 })).toHaveLength(3);
    expect(validateEquipmentForm({ reference: 'BAL-001', category_id: 'c1', total_units: '10' })).toEqual([]);
  });

  it('exige que las contraseñas del entrenador coincidan', () => {
    expect(validateCoachForm({ username: 'coach', password: 'a', passwordConfirmation: 'b' })).toContain('Las contraseñas no coinciden.');
  });
});
