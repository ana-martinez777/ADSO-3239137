<?php

namespace Tests\Feature;

use App\Application\Ports\SportsGearRepository;
use App\Models\User;
use Mockery;
use Tests\TestCase;

final class SportsGearAuthorizationTest extends TestCase
{
    private const ATHLETE_ID = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
    private const LOAN_ID = 'cccccccc-cccc-4ccc-8ccc-cccccccccccc';

    public function test_protected_category_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/categories')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'UNAUTHENTICATED');
    }

    public function test_athlete_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/athletes')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'UNAUTHENTICATED');

        $this->postJson('/api/v1/athletes', [])
            ->assertUnauthorized()
            ->assertJsonPath('code', 'UNAUTHENTICATED');
    }

    public function test_loan_consultation_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/loans')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'UNAUTHENTICATED');

        $this->getJson('/api/v1/loans/' . self::LOAN_ID)
            ->assertUnauthorized()
            ->assertJsonPath('code', 'UNAUTHENTICATED');

        $this->getJson('/api/v1/loans/overdue')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'UNAUTHENTICATED');
    }

    public function test_coach_cannot_create_equipment(): void
    {
        $coach = $this->userWithRole('coach');

        $this->actingAs($coach, 'api')
            ->postJson('/api/v1/equipment', [])
            ->assertForbidden()
            ->assertJsonPath('code', 'FORBIDDEN_ROLE');
    }

    public function test_admin_cannot_register_a_loan(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/loans', [])
            ->assertForbidden()
            ->assertJsonPath('code', 'FORBIDDEN_ROLE');
    }

    public function test_admin_and_coach_can_register_athletes(): void
    {
        $repository = Mockery::mock(SportsGearRepository::class);

        $repository->shouldReceive('athleteCreate')
            ->twice()
            ->with([
                'full_name' => 'Carlos Pérez',
                'document_number' => '123456789',
            ])
            ->andReturn([
                'id' => self::ATHLETE_ID,
                'full_name' => 'Carlos Pérez',
                'document_number' => '123456789',
            ]);

        $this->app->instance(SportsGearRepository::class, $repository);

        foreach (['admin', 'coach'] as $role) {
            $this->actingAs($this->userWithRole($role), 'api')
                ->postJson('/api/v1/athletes', [
                    'full_name' => 'Carlos Pérez',
                    'document_number' => '123456789',
                ])
                ->assertCreated()
                ->assertJsonPath('data.id', self::ATHLETE_ID);
        }
    }

    public function test_admin_and_coach_can_consult_athletes(): void
    {
        $repository = Mockery::mock(SportsGearRepository::class);

        $repository->shouldReceive('athletesList')
            ->twice()
            ->with([])
            ->andReturn([
                [
                    'id' => self::ATHLETE_ID,
                    'full_name' => 'Carlos Pérez',
                    'document_number' => '123456789',
                ],
            ]);

        $this->app->instance(SportsGearRepository::class, $repository);

        foreach (['admin', 'coach'] as $role) {
            $this->actingAs($this->userWithRole($role), 'api')
                ->getJson('/api/v1/athletes')
                ->assertOk()
                ->assertJsonPath('data.0.id', self::ATHLETE_ID);
        }
    }

    public function test_admin_and_coach_can_consult_loans(): void
    {
        $repository = Mockery::mock(SportsGearRepository::class);

        $repository->shouldReceive('loansList')
            ->twice()
            ->with([])
            ->andReturn([]);

        $this->app->instance(SportsGearRepository::class, $repository);

        foreach (['admin', 'coach'] as $role) {
            $this->actingAs($this->userWithRole($role), 'api')
                ->getJson('/api/v1/loans')
                ->assertOk()
                ->assertExactJson(['data' => []]);
        }
    }

    public function test_admin_and_coach_can_consult_loan_details(): void
    {
        $repository = Mockery::mock(SportsGearRepository::class);

        $repository->shouldReceive('loanFind')
            ->twice()
            ->with(self::LOAN_ID)
            ->andReturn([
                'id' => self::LOAN_ID,
            ]);

        $this->app->instance(SportsGearRepository::class, $repository);

        foreach (['admin', 'coach'] as $role) {
            $this->actingAs($this->userWithRole($role), 'api')
                ->getJson('/api/v1/loans/' . self::LOAN_ID)
                ->assertOk()
                ->assertJsonPath('data.id', self::LOAN_ID);
        }
    }

    public function test_admin_and_coach_can_consult_overdue_loans(): void
    {
        $repository = Mockery::mock(SportsGearRepository::class);

        $repository->shouldReceive('overdueList')
            ->twice()
            ->with(null)
            ->andReturn([]);

        $this->app->instance(SportsGearRepository::class, $repository);

        foreach (['admin', 'coach'] as $role) {
            $this->actingAs($this->userWithRole($role), 'api')
                ->getJson('/api/v1/loans/overdue')
                ->assertOk()
                ->assertExactJson(['data' => []]);
        }
    }

    private function userWithRole(string $role): User
    {
        $user = new User();

        $user->setRawAttributes([
            'id' => $role === 'admin'
                ? 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'
                : 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
            'username' => $role,
            'password_hash' => 'not-used',
            'role' => $role,
        ]);

        return $user;
    }
}