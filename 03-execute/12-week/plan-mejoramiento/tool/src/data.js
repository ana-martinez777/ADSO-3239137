// Demo dataset. Names and document numbers are fictitious.

export const CATEGORY_PREFIXES = {
  Balones: 'BAL',
  Raquetas: 'RAQ',
  'Protección': 'PRO',
  Atletismo: 'ATL',
  Gimnasio: 'GIM',
};

const UNITS_PATTERN = [6, 4, 8, 3, 5, 10, 4, 6];

/** 8 items per category: 40 items with references such as BAL-001. */
export function buildEquipment() {
  return Object.entries(CATEGORY_PREFIXES).flatMap(([category, prefix]) =>
    UNITS_PATTERN.map((totalUnits, index) => ({
      reference: `${prefix}-${String(index + 1).padStart(3, '0')}`,
      category,
      totalUnits,
    })),
  );
}

export const ATHLETES = [
  { fullName: 'Laura Gómez Rojas', documentNumber: '1075210001' },
  { fullName: 'Andrés Felipe Perdomo', documentNumber: '1075210002' },
  { fullName: 'Valentina Cuéllar Díaz', documentNumber: '1075210003' },
  { fullName: 'Juan David Trujillo', documentNumber: '1075210004' },
  { fullName: 'Mariana Polanía Ríos', documentNumber: '1075210005' },
  { fullName: 'Santiago Medina Charry', documentNumber: '1075210006' },
  { fullName: 'Daniela Vargas Ospina', documentNumber: '1075210007' },
  { fullName: 'Camilo Andrés Losada', documentNumber: '1075210008' },
  { fullName: 'Isabela Rojas Cabrera', documentNumber: '1075210009' },
  { fullName: 'Sebastián Murcia Silva', documentNumber: '1075210010' },
];

export const COACH_USERNAMES = ['entrenador01', 'entrenador02'];

/**
 * Loan plan by index (athletes 0-9, equipment 0-39):
 * - overdue: 3 one-unit loans registered on the API whose clock runs 3 days behind.
 * - first: 7 loans of 2 units, one per athlete without overdue items.
 * - returned: the first 5 of those loans are fully returned.
 * - second: 5 new loans for the athletes who returned everything.
 * Total: 15 loans, never more than 3 pending units per athlete.
 */
export function buildLoanPlan() {
  const overdue = [7, 8, 9].map((athlete, i) => ({ athlete, items: [{ equipment: 3 + i * 8, quantity: 1 }] }));
  const first = [0, 1, 2, 3, 4, 5, 6].map((athlete) => ({
    athlete,
    items: [
      { equipment: athlete % 8, quantity: 1 },
      { equipment: 8 + ((athlete * 3) % 8), quantity: 1 },
    ],
  }));
  const returned = [0, 1, 2, 3, 4];
  const second = [0, 1, 2, 3, 4].map((athlete) => ({
    athlete,
    items: [
      { equipment: 16 + athlete, quantity: 1 },
      { equipment: 32 + (athlete % 3), quantity: athlete % 2 === 0 ? 2 : 1 },
    ],
  }));
  return { overdue, first, returned, second };
}
