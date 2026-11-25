import { describe, expect, it, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import NewLoanPage, { buildLoanPayload } from './NewLoanPage';
import { jsonResponse } from '../test/setup';

const athletes = [{ id: 'a1', full_name: 'Laura Gómez', document_number: '1075' }];
const equipment = [
  { id: 'e1', reference: 'BAL-001', category: { id: 'c1', name: 'Balones' }, total_units: 5, available_units: 4, deleted_at: null },
];

function mockApi(onLoanPost) {
  return vi.spyOn(globalThis, 'fetch').mockImplementation(async (url, options = {}) => {
    const path = String(url);
    if (path.includes('/athletes')) return jsonResponse(200, { data: athletes });
    if (path.includes('/equipment')) return jsonResponse(200, { data: equipment });
    if (path.endsWith('/loans') && options.method === 'POST') return onLoanPost(JSON.parse(options.body));
    return jsonResponse(404, { code: 'NOT_FOUND', message: 'No encontrado', details: {} });
  });
}

function renderPage() {
  return render(
    <MemoryRouter initialEntries={['/loans/new']}>
      <Routes>
        <Route path="/loans/new" element={<NewLoanPage />} />
        <Route path="/loans/:id" element={<p>Detalle creado</p>} />
      </Routes>
    </MemoryRouter>,
  );
}

describe('registro de préstamos (HU-06)', () => {
  it('construye el cuerpo exacto del contrato POST /loans', () => {
    expect(buildLoanPayload('a1', [{ equipmentId: 'e1', quantity: '2' }, { equipmentId: '', quantity: 1 }])).toEqual({
      athlete_id: 'a1',
      items: [{ equipment_id: 'e1', quantity: 2 }],
    });
  });

  it('no envía el préstamo si supera las unidades permitidas', async () => {
    const post = vi.fn();
    mockApi(post);
    renderPage();
    await userEvent.selectOptions(await screen.findByLabelText('Deportista'), 'a1');
    await userEvent.selectOptions(screen.getByLabelText('Implemento 1'), 'e1');
    const quantity = screen.getByLabelText('Cantidad');
    await userEvent.clear(quantity);
    await userEvent.type(quantity, '4');
    await userEvent.click(screen.getByRole('button', { name: 'Registrar préstamo' }));

    expect(screen.getByRole('alert')).toHaveTextContent('máximo 3 unidades');
    expect(post).not.toHaveBeenCalled();
  });

  it('muestra la regla de negocio que devuelve la API', async () => {
    mockApi(() => jsonResponse(409, { code: 'ATHLETE_HAS_OVERDUE_LOANS', message: 'El deportista tiene préstamos vencidos.', details: {} }));
    renderPage();
    await userEvent.selectOptions(await screen.findByLabelText('Deportista'), 'a1');
    await userEvent.selectOptions(screen.getByLabelText('Implemento 1'), 'e1');
    await userEvent.click(screen.getByRole('button', { name: 'Registrar préstamo' }));

    expect(await screen.findByText('El deportista tiene préstamos vencidos.')).toBeInTheDocument();
  });

  it('abre el detalle del préstamo cuando la API lo registra', async () => {
    mockApi((body) => jsonResponse(201, { data: { id: 'l1', ...body, items: [] } }));
    renderPage();
    await userEvent.selectOptions(await screen.findByLabelText('Deportista'), 'a1');
    await userEvent.selectOptions(screen.getByLabelText('Implemento 1'), 'e1');
    await userEvent.click(screen.getByRole('button', { name: 'Registrar préstamo' }));

    expect(await screen.findByText('Detalle creado')).toBeInTheDocument();
  });
});
