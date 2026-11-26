import { describe, expect, it } from 'vitest';
import { can, hasRole, homePathFor, navItemsFor, ROUTE_ROLES } from './permissions';

const paths = (role) => navItemsFor(role).map((item) => item.path);

describe('role permissions (api-contract.md, table 8.3)', () => {
  it('admin sees coaches and the report, but not loan registration', () => {
    expect(paths('admin')).toEqual(expect.arrayContaining(['/coaches', '/reports/most-requested', '/equipment', '/loans', '/overdue']));
    expect(paths('admin')).not.toContain('/loans/new');
  });

  it('coach registers loans, but does not see coaches or the report', () => {
    expect(paths('coach')).toContain('/loans/new');
    expect(paths('coach')).not.toContain('/coaches');
    expect(paths('coach')).not.toContain('/reports/most-requested');
  });

  it('actions match the authorization table', () => {
    expect(can.manageEquipment('admin')).toBe(true);
    expect(can.manageEquipment('coach')).toBe(false);
    expect(can.viewInactiveEquipment('coach')).toBe(false);
    expect(can.registerLoan('coach')).toBe(true);
    expect(can.registerLoan('admin')).toBe(false);
    expect(can.returnItem('admin')).toBe(false);
    expect(can.viewReport('coach')).toBe(false);
  });

  it('checks the user role against a route', () => {
    expect(hasRole({ role: 'coach' }, ROUTE_ROLES.report)).toBe(false);
    expect(hasRole({ role: 'admin' }, ROUTE_ROLES.report)).toBe(true);
    expect(hasRole(null, ROUTE_ROLES.equipment)).toBe(false);
  });

  it('each role has an allowed home screen', () => {
    expect(paths('admin')).toContain(homePathFor('admin'));
    expect(paths('coach')).toContain(homePathFor('coach'));
  });
});
