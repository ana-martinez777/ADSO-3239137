<?php

namespace Tests\Integration;

use App\Application\Ports\Clock;
use App\Application\Ports\SportsGearRepository;
use DateTimeImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * T-18 and T-15: run against the configured MySQL database. Every test runs
 * inside a transaction that is rolled back, so no data is left behind.
 * Skipped when the connection is not MySQL/MariaDB or the server is down.
 */
final class MySqlConstraintsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            self::markTestSkipped('Requires a MySQL connection.');
        }
        try {
            DB::connection()->getPdo();
        } catch (\Throwable) {
            self::markTestSkipped('MySQL is not reachable.');
        }
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    public function test_equipment_total_must_be_at_least_one(): void
    {
        $this->expectException(QueryException::class);
        $this->insertEquipment(totalUnits: 0, availableUnits: 0);
    }

    public function test_available_units_cannot_be_negative(): void
    {
        $this->expectException(QueryException::class);
        $this->insertEquipment(totalUnits: 3, availableUnits: -1);
    }

    public function test_available_units_cannot_exceed_total(): void
    {
        $this->expectException(QueryException::class);
        $this->insertEquipment(totalUnits: 3, availableUnits: 4);
    }

    public function test_equipment_reference_is_unique(): void
    {
        $this->insertEquipment(totalUnits: 3, availableUnits: 3, reference: 'TEST-UNIQUE');
        $this->expectException(QueryException::class);
        $this->insertEquipment(totalUnits: 3, availableUnits: 3, reference: 'TEST-UNIQUE');
    }

    public function test_loan_item_quantity_must_be_positive_and_equipment_unique_per_loan(): void
    {
        [$loanId, $equipmentId] = $this->insertLoan();
        $this->insertLoanItem($loanId, $equipmentId, 1);

        try {
            $this->insertLoanItem($loanId, $this->insertEquipment(3, 3), 0);
            self::fail('A zero quantity should be rejected.');
        } catch (QueryException) {
            self::assertTrue(true);
        }

        $this->expectException(QueryException::class);
        $this->insertLoanItem($loanId, $equipmentId, 1);
    }

    public function test_user_role_is_restricted(): void
    {
        $this->expectException(QueryException::class);
        DB::table('users')->insert(['id' => (string) Str::uuid(), 'username' => 'test-'.Str::random(8), 'password_hash' => 'x', 'role' => 'athlete']);
    }

    public function test_loan_due_date_comes_from_the_clock_port(): void
    {
        $fixedNow = new DateTimeImmutable('2026-01-10T15:00:00+00:00');
        $this->app->instance(Clock::class, new class($fixedNow) implements Clock {
            public function __construct(private readonly DateTimeImmutable $now) {}
            public function now(): DateTimeImmutable { return $this->now; }
        });

        $athleteId = $this->insertAthlete();
        $equipmentId = $this->insertEquipment(3, 3);
        $loan = $this->app->make(SportsGearRepository::class)
            ->loanCreate($athleteId, $this->insertUser(), [['equipment_id' => $equipmentId, 'quantity' => 1]]);

        self::assertStringStartsWith('2026-01-10T15:00:00', $loan['loaned_at']);
        self::assertStringStartsWith('2026-01-12T15:00:00', $loan['items'][0]['due_at']);
    }

    private function categoryId(): string
    {
        $id = DB::table('categories')->value('id');
        if ($id === null) {
            $id = (string) Str::uuid();
            DB::table('categories')->insert(['id' => $id, 'name' => 'Balones']);
        }

        return $id;
    }

    private function insertEquipment(int $totalUnits, int $availableUnits, ?string $reference = null): string
    {
        $id = (string) Str::uuid();
        DB::table('equipment')->insert([
            'id' => $id,
            'reference' => $reference ?? 'TEST-'.Str::random(10),
            'category_id' => $this->categoryId(),
            'total_units' => $totalUnits,
            'available_units' => $availableUnits,
        ]);

        return $id;
    }

    private function insertAthlete(): string
    {
        $id = (string) Str::uuid();
        DB::table('athletes')->insert(['id' => $id, 'full_name' => 'Test Athlete', 'document_number' => 'T'.Str::random(12)]);

        return $id;
    }

    private function insertUser(): string
    {
        $id = (string) Str::uuid();
        DB::table('users')->insert(['id' => $id, 'username' => 'test-'.Str::random(8), 'password_hash' => 'x', 'role' => 'coach']);

        return $id;
    }

    /** @return array{0: string, 1: string} */
    private function insertLoan(): array
    {
        $loanId = (string) Str::uuid();
        DB::table('loans')->insert([
            'id' => $loanId,
            'athlete_id' => $this->insertAthlete(),
            'user_id' => $this->insertUser(),
            'loaned_at' => '2026-01-10 15:00:00',
        ]);

        return [$loanId, $this->insertEquipment(3, 3)];
    }

    private function insertLoanItem(string $loanId, string $equipmentId, int $quantity): void
    {
        DB::table('loan_items')->insert([
            'id' => (string) Str::uuid(),
            'loan_id' => $loanId,
            'equipment_id' => $equipmentId,
            'equipment_reference' => 'TEST',
            'category_name' => 'Balones',
            'quantity' => $quantity,
            'due_at' => '2026-01-12 15:00:00',
        ]);
    }
}
