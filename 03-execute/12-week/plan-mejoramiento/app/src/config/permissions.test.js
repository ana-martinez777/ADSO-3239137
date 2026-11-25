import { describe, expect, it } from 'vitest';
import { can, hasRole, homePathFor, navItemsFor, ROUTE_ROLES } from './permissions';

const paths = (role) => navItemsFor(role).map((item) => item.path);

describe('permisos por rol (api-contract.md, tabla 8.3)', () => {
  it('el administrador ve entrenadores y reporte, pero no el registro de préstamos', () => {
    expect(paths('admin')).toEqual(expect.arrayContaining(['/coaches', '/reports/most-requested', '/equipment', '/loans', '/overdue']));
    expect(paths('admin')).not.toContain('/loans/new');
  });

  it('el entrenador registra préstamos, pero no ve entrenadores ni reporte', () => {
    expect(paths('coach')).toContain('/loans/new');
    expect(paths('coach')).not.toContain('/coaches');
    expect(paths('coach')).not.toContain('/reports/most-requested');
  });

  it('las acciones coinciden con la tabla de autorización', () => {
    expect(can.manageEquipment('admin')).toBe(true);
    expect(can.manageEquipment('coach')).toBe(false);
    expect(can.viewInactiveEquipment('coach')).toBe(false);
    expect(can.registerLoan('coach')).toBe(true);
    expect(can.registerLoan('admin')).toBe(false);
    expect(can.returnItem('admin')).toBe(false);
    expect(can.viewReport('coach')).toBe(false);
  });

  it('valida el rol del usuario contra una ruta', () => {
    expect(hasRole({ role: 'coach' }, ROUTE_ROLES.report)).toBe(false);
    expect(hasRole({ role: 'admin' }, ROUTE_ROLES.report)).toBe(true);
    expect(hasRole(null, ROUTE_ROLES.equipment)).toBe(false);
  });

  it('cada rol tiene una pantalla de inicio permitida', () => {
    expect(paths('admin')).toContain(homePathFor('admin'));
    expect(paths('coach')).toContain(homePathFor('coach'));
  });
});
