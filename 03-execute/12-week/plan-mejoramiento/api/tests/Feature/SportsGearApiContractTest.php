<?php

namespace Tests\Feature;

use App\Application\Ports\SportsGearRepository;
use App\Models\User;
use Mockery;
use Tests\TestCase;

final class SportsGearApiContractTest extends TestCase
{
    public function test_authenticated_user_can_consult_categories(): void
    {
        $repository = Mockery::mock(SportsGearRepository::class);

        $repository->shouldReceive('categories')
            ->once()
            ->andReturn([
                [
                    'id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
                    'name' => 'Balones',
                ],
            ]);

        $this->app->instance(SportsGearRepository::class, $repository);

        $user = $this->userWithRole('coach');

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/categories')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Balones');
    }

    public function test_coach_can_list_active_equipment(): void
    {
        $repository = Mockery::mock(SportsGearRepository::class);

        $repository->shouldReceive('equipmentList')
            ->once()
            ->with([], 'coach')
            ->andReturn([]);

        $this->app->instance(SportsGearRepository::class, $repository);

        $user = $this->userWithRole('coach');

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/equipment')
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }

    public function test_authenticated_user_can_consult_athletes_with_filters(): void
    {
        $repository = Mockery::mock(SportsGearRepository::class);

        $repository->shouldReceive('athletesList')
            ->once()
            ->with([
                'search' => 'Carlos',
                'document_number' => '123456789',
            ])
            ->andReturn([]);

        $this->app->instance(SportsGearRepository::class, $repository);

        $user = $this->userWithRole('admin');

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/athletes?search=Carlos&document_number=123456789')
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }

    public function test_authenticated_user_can_consult_athlete_details(): void
    {
        $athleteId = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';

        $repository = Mockery::mock(SportsGearRepository::class);

        $repository->shouldReceive('athleteFind')
            ->once()
            ->with($athleteId)
            ->andReturn([
                'id' => $athleteId,
                'full_name' => 'Carlos Pérez',
                'document_number' => '123456789',
            ]);

        $this->app->instance(SportsGearRepository::class, $repository);

        $user = $this->userWithRole('coach');

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/athletes/' . $athleteId)
            ->assertOk()
            ->assertJsonPath('data.full_name', 'Carlos Pérez');
    }

    public function test_authenticated_user_can_consult_loans_with_filters(): void
    {
        $repository = Mockery::mock(SportsGearRepository::class);

        $repository->shouldReceive('loansList')
            ->once()
            ->with([
                'status' => 'pending',
            ])
            ->andReturn([]);

        $this->app->instance(SportsGearRepository::class, $repository);

        $user = $this->userWithRole('admin');

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/loans?status=pending')
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }

    public function test_authenticated_user_can_consult_overdue_loans_by_athlete(): void
    {
        $athleteId = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';

        $repository = Mockery::mock(SportsGearRepository::class);

        $repository->shouldReceive('overdueList')
            ->once()
            ->with($athleteId)
            ->andReturn([]);

        $this->app->instance(SportsGearRepository::class, $repository);

        $user = $this->userWithRole('coach');

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/loans/overdue?athlete_id=' . $athleteId)
            ->assertOk()
            ->assertExactJson(['data' => []]);
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