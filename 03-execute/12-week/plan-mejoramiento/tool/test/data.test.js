import { test } from 'node:test';
import assert from 'node:assert/strict';
import { ATHLETES, CATEGORY_PREFIXES, COACH_USERNAMES, buildEquipment, buildLoanPlan } from '../src/data.js';

const equipment = buildEquipment();
const plan = buildLoanPlan();

test('dataset meets the T-21 minimums', () => {
  assert.ok(equipment.length >= 40);
  assert.ok(ATHLETES.length >= 10);
  const loans = plan.overdue.length + plan.first.length + plan.second.length;
  assert.ok(loans >= 15);
  assert.ok(plan.overdue.length >= 3);
});

test('references and document numbers are unique', () => {
  assert.equal(new Set(equipment.map((e) => e.reference)).size, equipment.length);
  assert.equal(new Set(ATHLETES.map((a) => a.documentNumber)).size, ATHLETES.length);
  assert.equal(new Set(COACH_USERNAMES).size, COACH_USERNAMES.length);
});

test('every item belongs to one of the five seeded categories', () => {
  const categories = Object.keys(CATEGORY_PREFIXES);
  assert.equal(categories.length, 5);
  equipment.forEach((item) => assert.ok(categories.includes(item.category)));
});

test('plan respects the business rules of the API', () => {
  const pending = new Map();
  const loaned = new Map();
  const add = (entry, sign) => {
    const ids = entry.items.map((i) => i.equipment);
    assert.equal(new Set(ids).size, ids.length, 'equipment repeated in one loan');
    entry.items.forEach((item) => {
      assert.ok(item.quantity >= 1);
      pending.set(entry.athlete, (pending.get(entry.athlete) ?? 0) + sign * item.quantity);
      loaned.set(item.equipment, (loaned.get(item.equipment) ?? 0) + sign * item.quantity);
      assert.ok(loaned.get(item.equipment) <= equipment[item.equipment].totalUnits, 'not enough units');
    });
    assert.ok(pending.get(entry.athlete) <= 3, 'more than 3 pending units');
  };

  plan.overdue.forEach((entry) => add(entry, 1));
  const overdueAthletes = new Set(plan.overdue.map((e) => e.athlete));
  plan.first.forEach((entry) => {
    assert.ok(!overdueAthletes.has(entry.athlete), 'athlete with overdue items cannot borrow');
    add(entry, 1);
  });
  plan.returned.forEach((index) => add(plan.first[index], -1));
  plan.second.forEach((entry) => {
    assert.ok(!overdueAthletes.has(entry.athlete));
    add(entry, 1);
  });
});
