import { describe, expect, it } from 'vitest';
import { errorMessage, formatDateTime, invalidFieldLabels, loanStatusLabel } from './format';
import { ApiError } from '../api/client';

describe('presentation format', () => {
  it('translates the computed loan statuses', () => {
    expect(loanStatusLabel('pending')).toBe('Pendiente');
    expect(loanStatusLabel('overdue')).toBe('Vencido');
    expect(loanStatusLabel('returned')).toBe('Devuelto');
  });

  it('shows a dash when there is no date', () => {
    expect(formatDateTime(null)).toBe('—');
    expect(formatDateTime('no-es-fecha')).toBe('—');
    expect(formatDateTime('2026-11-15T14:00:00Z')).toMatch(/2026/);
  });

  it('converts invalid fields into Spanish labels', () => {
    expect(invalidFieldLabels({ 'items.0.quantity': ['x'], athlete_id: ['y'] })).toEqual(['Implementos', 'Deportista']);
  });

  it('adds the fields to review to validation errors', () => {
    const error = new ApiError(422, 'VALIDATION_ERROR', 'Los datos enviados no cumplen las validaciones.', { reference: ['required'] });
    expect(errorMessage(error)).toBe('Los datos enviados no cumplen las validaciones. Revisa: Referencia.');
  });
});
