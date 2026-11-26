// UI permissions based on table 8.3 of docs/api-contract.md.
// The UI only hides options; the API enforces the actual authorization.

export const ROLES = Object.freeze({ ADMIN: 'admin', COACH: 'coach' });

const BOTH = [ROLES.ADMIN, ROLES.COACH];

export const ROUTE_ROLES = Object.freeze({
  equipment: BOTH,
  athletes: BOTH,
  loans: BOTH,
  newLoan: [ROLES.COACH],
  overdue: BOTH,
  coaches: [ROLES.ADMIN],
  report: [ROLES.ADMIN],
});

export const NAV_ITEMS = [
  { path: '/equipment', label: 'Catálogo', roles: ROUTE_ROLES.equipment },
  { path: '/athletes', label: 'Deportistas', roles: ROUTE_ROLES.athletes },
  { path: '/loans/new', label: 'Nuevo préstamo', roles: ROUTE_ROLES.newLoan },
  { path: '/loans', label: 'Préstamos', roles: ROUTE_ROLES.loans },
  { path: '/overdue', label: 'Vencidos', roles: ROUTE_ROLES.overdue },
  { path: '/coaches', label: 'Entrenadores', roles: ROUTE_ROLES.coaches },
  { path: '/reports/most-requested', label: 'Más solicitados', roles: ROUTE_ROLES.report },
];

export const ROLE_LABELS = Object.freeze({ admin: 'Administrador', coach: 'Entrenador' });

export function navItemsFor(role) {
  return NAV_ITEMS.filter((item) => item.roles.includes(role));
}

export function hasRole(user, roles) {
  return Boolean(user) && roles.includes(user.role);
}

export const can = {
  manageEquipment: (role) => role === ROLES.ADMIN,
  viewInactiveEquipment: (role) => role === ROLES.ADMIN,
  registerLoan: (role) => role === ROLES.COACH,
  returnItem: (role) => role === ROLES.COACH,
  manageCoaches: (role) => role === ROLES.ADMIN,
  viewReport: (role) => role === ROLES.ADMIN,
};

export function homePathFor(role) {
  return role === ROLES.COACH ? '/loans/new' : '/equipment';
}
