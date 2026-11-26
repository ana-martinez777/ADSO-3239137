// Operations from docs/api-contract.md (prefix /api/v1).
// Each function returns the response "data" property directly.
import { request } from './client';

const data = (promise) => promise.then((payload) => payload?.data ?? null);

export const authApi = {
  login: (username, password) => data(request('/auth/login', { method: 'POST', body: { username, password } })),
};

export const coachesApi = {
  list: (filters) => data(request('/users/coaches', { query: filters })),
  create: (coach) => data(request('/users/coaches', { method: 'POST', body: coach })),
};

export const categoriesApi = {
  list: () => data(request('/categories')),
};

export const equipmentApi = {
  list: (filters) => data(request('/equipment', { query: filters })),
  find: (id) => data(request(`/equipment/${id}`)),
  create: (equipment) => data(request('/equipment', { method: 'POST', body: equipment })),
  update: (id, equipment) => data(request(`/equipment/${id}`, { method: 'PUT', body: equipment })),
  deactivate: (id) => request(`/equipment/${id}`, { method: 'DELETE' }),
};

export const athletesApi = {
  list: (filters) => data(request('/athletes', { query: filters })),
  find: (id) => data(request(`/athletes/${id}`)),
  create: (athlete) => data(request('/athletes', { method: 'POST', body: athlete })),
};

export const loansApi = {
  list: (filters) => data(request('/loans', { query: filters })),
  find: (id) => data(request(`/loans/${id}`)),
  create: (loan) => data(request('/loans', { method: 'POST', body: loan })),
  overdue: (filters) => data(request('/loans/overdue', { query: filters })),
  returnItem: (loanId, itemId) => data(request(`/loans/${loanId}/items/${itemId}/return`, { method: 'POST' })),
};

export const reportsApi = {
  mostRequested: (filters) => data(request('/reports/most-requested', { query: filters })),
};
