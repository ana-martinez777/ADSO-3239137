import { describe, expect, it } from 'vitest';
import { validateCoachForm, validateDateRange, validateEquipmentForm, validateLoanDraft } from './forms';

const equipment = new Map([
  ['e1', { reference: 'BAL-001', available_units: 5 }],
  ['e2', { reference: 'RAQ-001', available_units: 1 }],
]);

describe('complementary loan validation', () => {
  it('accepts a valid loan of up to three units', () => {
    const draft = { athleteId: 'a1', items: [{ equipmentId: 'e1', quantity: 2 }, { equipmentId: 'e2', quantity: 1 }] };
    expect(validateLoanDraft(draft, equipment)).toEqual([]);
  });

  it('requires an athlete and at least one equipment item', () => {
    const errors = validateLoanDraft({ athleteId: '', items: [{ equipmentId: '', quantity: 1 }] }, equipment);
    expect(errors).toHaveLength(2);
  });

  it('rejects repeated equipment', () => {
    const draft = { athleteId: 'a1', items: [{ equipmentId: 'e1', quantity: 1 }, { equipmentId: 'e1', quantity: 1 }] };
    expect(validateLoanDraft(draft, equipment)).toContain('Un mismo implemento no puede repetirse en el préstamo.');
  });

  it('rejects more than three units and quantities above availability', () => {
    const draft = { athleteId: 'a1', items: [{ equipmentId: 'e1', quantity: 3 }, { equipmentId: 'e2', quantity: 2 }] };
    const errors = validateLoanDraft(draft, equipment);
    expect(errors.some((e) => e.includes('máximo 3 unidades'))).toBe(true);
    expect(errors.some((e) => e.includes('RAQ-001'))).toBe(true);
  });

  it('rejects zero or decimal quantities', () => {
    const draft = { athleteId: 'a1', items: [{ equipmentId: 'e1', quantity: 0 }] };
    expect(validateLoanDraft(draft, equipment)[0]).toContain('mayor que cero');
  });
});

describe('period validation', () => {
  it('allows querying without dates or with both dates in order', () => {
    expect(validateDateRange('', '')).toBeNull();
    expect(validateDateRange('2026-11-01', '2026-11-30')).toBeNull();
  });

  it('requires both dates and a start date not after the end date', () => {
    expect(validateDateRange('2026-11-01', '')).toMatch(/fecha inicial y la fecha final/);
    expect(validateDateRange('2026-11-30', '2026-11-01')).toMatch(/posterior/);
  });
});

describe('catalog and coach forms', () => {
  it('validates reference, category and a minimum total of one unit', () => {
    expect(validateEquipmentForm({ reference: ' ', category_id: '', total_units: 0 })).toHaveLength(3);
    expect(validateEquipmentForm({ reference: 'BAL-001', category_id: 'c1', total_units: '10' })).toEqual([]);
  });

  it('requires the coach passwords to match', () => {
    expect(validateCoachForm({ username: 'coach', password: 'a', passwordConfirmation: 'b' })).toContain('Las contraseñas no coinciden.');
  });
});
