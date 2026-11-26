// Seeds Sports Gear with demo data using only the API (T-21).
// Run with Docker:  docker compose -f infra/docker-compose.yml --profile seed run --rm seeder
import { ApiClient, ApiError, waitForApi } from './apiClient.js';
import { loadConfig } from './config.js';
import { ATHLETES, COACH_USERNAMES, buildEquipment, buildLoanPlan } from './data.js';

const MIN = { equipment: 40, athletes: 10, loans: 15, overdue: 3 };

function log(message) {
  console.log(`[sembrador] ${message}`);
}

async function ensureCoaches(admin, password) {
  for (const username of COACH_USERNAMES) {
    try {
      await admin.post('/users/coaches', { username, password });
      log(`Entrenador ${username} registrado.`);
    } catch (error) {
      if (!(error instanceof ApiError) || error.status !== 409) throw error;
      log(`Entrenador ${username} ya existía.`);
    }
  }
}

async function ensureEquipment(admin) {
  const categories = await admin.get('/categories');
  const categoryId = new Map(categories.map((category) => [category.name, category.id]));
  const existing = new Map((await admin.get('/equipment', { status: 'all' })).map((item) => [item.reference, item]));

  const result = [];
  let created = 0;
  for (const item of buildEquipment()) {
    let equipment = existing.get(item.reference);
    if (!equipment) {
      equipment = await admin.post('/equipment', {
        reference: item.reference,
        category_id: categoryId.get(item.category),
        total_units: item.totalUnits,
      });
      created += 1;
    }
    result.push(equipment);
  }
  log(`Implementos: ${created} registrados, ${result.length - created} ya existían.`);
  return result;
}

async function ensureAthletes(admin) {
  const existing = new Map((await admin.get('/athletes')).map((athlete) => [athlete.document_number, athlete]));

  const result = [];
  let created = 0;
  for (const athlete of ATHLETES) {
    let record = existing.get(athlete.documentNumber);
    if (!record) {
      record = await admin.post('/athletes', { full_name: athlete.fullName, document_number: athlete.documentNumber });
      created += 1;
    }
    result.push(record);
  }
  log(`Deportistas: ${created} registrados, ${result.length - created} ya existían.`);
  return result;
}

function loanBody(entry, athletes, equipment) {
  return {
    athlete_id: athletes[entry.athlete].id,
    items: entry.items.map((item) => ({ equipment_id: equipment[item.equipment].id, quantity: item.quantity })),
  };
}

async function seedLoans({ coachNow, coachPast, coachTwo }, athletes, equipment) {
  const plan = buildLoanPlan();

  // The past-clock API stores these loans 3 days ago, so they are already overdue.
  for (const entry of plan.overdue) {
    await coachPast.post('/loans', loanBody(entry, athletes, equipment));
  }
  log(`${plan.overdue.length} préstamos registrados con fecha de hace 3 días (vencidos).`);

  const firstLoans = [];
  for (const entry of plan.first) {
    firstLoans.push(await coachNow.post('/loans', loanBody(entry, athletes, equipment)));
  }
  log(`${firstLoans.length} préstamos registrados hoy.`);

  let returnedItems = 0;
  for (const index of plan.returned) {
    const loan = await coachNow.get(`/loans/${firstLoans[index].id}`);
    for (const item of loan.items) {
      await coachNow.post(`/loans/${loan.id}/items/${item.id}/return`);
      returnedItems += 1;
    }
  }
  log(`${returnedItems} devoluciones registradas (${plan.returned.length} préstamos completos).`);

  for (const entry of plan.second) {
    await coachTwo.post('/loans', loanBody(entry, athletes, equipment));
  }
  log(`${plan.second.length} préstamos registrados por ${COACH_USERNAMES[1]}.`);
}

async function verify(admin) {
  const counts = {
    equipment: (await admin.get('/equipment', { status: 'all' })).length,
    athletes: (await admin.get('/athletes')).length,
    loans: (await admin.get('/loans')).length,
    overdue: (await admin.get('/loans/overdue')).length,
  };
  const reportRows = (await admin.get('/reports/most-requested')).length;

  log(`Resultado: ${counts.equipment} implementos, ${counts.athletes} deportistas, ${counts.loans} préstamos, ${counts.overdue} vencidos, ${reportRows} filas en el reporte.`);

  const missing = Object.entries(MIN).filter(([key, min]) => counts[key] < min).map(([key]) => key);
  if (reportRows === 0) missing.push('report');
  return missing;
}

export async function run(config) {
  const admin = new ApiClient(config.apiUrl);
  await waitForApi(admin);
  await waitForApi(new ApiClient(config.apiPastUrl));

  await admin.login(config.admin.username, config.admin.password);
  log('Sesión iniciada como administrador.');

  await ensureCoaches(admin, config.coachPassword);
  const equipment = await ensureEquipment(admin);
  const athletes = await ensureAthletes(admin);

  if ((await admin.get('/loans')).length > 0) {
    log('Ya existen préstamos: no se registran nuevos para no duplicar datos.');
  } else {
    const coachNow = new ApiClient(config.apiUrl);
    const coachPast = new ApiClient(config.apiPastUrl);
    const coachTwo = new ApiClient(config.apiUrl);
    await coachNow.login(COACH_USERNAMES[0], config.coachPassword);
    await coachPast.login(COACH_USERNAMES[0], config.coachPassword);
    await coachTwo.login(COACH_USERNAMES[1], config.coachPassword);
    await seedLoans({ coachNow, coachPast, coachTwo }, athletes, equipment);
  }

  return verify(admin);
}

const isMain = import.meta.url === `file://${process.argv[1]}` || process.argv[1]?.endsWith('seed.js');
if (isMain) {
  try {
    const missing = await run(loadConfig());
    if (missing.length > 0) {
      console.error(`[sembrador] No se alcanzaron los mínimos de T-21: ${missing.join(', ')}.`);
      process.exit(1);
    }
    log('Sembrado completo.');
  } catch (error) {
    const detail = error instanceof ApiError ? ` (${error.code}, HTTP ${error.status})` : '';
    console.error(`[sembrador] Error: ${error.message}${detail}`);
    process.exit(1);
  }
}
