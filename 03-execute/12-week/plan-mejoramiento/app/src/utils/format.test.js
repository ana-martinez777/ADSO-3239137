import { describe, expect, it } from 'vitest';
import { errorMessage, formatDateTime, invalidFieldLabels, loanStatusLabel } from './format';
import { ApiError } from '../api/client';

describe('formato de presentación', () => {
  it('traduce los estados calculados del préstamo', () => {
    expect(loanStatusLabel('pending')).toBe('Pendiente');
    expect(loanStatusLabel('overdue')).toBe('Vencido');
    expect(loanStatusLabel('returned')).toBe('Devuelto');
  });

  it('muestra un guion cuando no hay fecha', () => {
    expect(formatDateTime(null)).toBe('—');
    expect(formatDateTime('no-es-fecha')).toBe('—');
    expect(formatDateTime('2026-11-15T14:00:00Z')).toMatch(/2026/);
  });

  it('convierte los campos inválidos en etiquetas en español', () => {
    expect(invalidFieldLabels({ 'items.0.quantity': ['x'], athlete_id: ['y'] })).toEqual(['Implementos', 'Deportista']);
  });

  it('agrega los campos a revisar en los errores de validación', () => {
    const error = new ApiError(422, 'VALIDATION_ERROR', 'Los datos enviados no cumplen las validaciones.', { reference: ['required'] });
    expect(errorMessage(error)).toBe('Los datos enviados no cumplen las validaciones. Revisa: Referencia.');
  });
});
