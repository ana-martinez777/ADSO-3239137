<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

final class SportsGearValidationTest extends TestCase
{
    public function test_loan_requires_at_least_one_item(): void
    {
        $coach = new User();
        $coach->setRawAttributes([
            'id' => 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
            'username' => 'coach03',
            'password_hash' => 'not-used',
            'role' => 'coach',
        ]);

        $this->actingAs($coach, 'api')
            ->postJson('/api/v1/loans', [
                'athlete_id' => 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee',
                'items' => [],
            ])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['code', 'message', 'details']);
    }

    public function test_loan_item_quantity_must_be_positive(): void
    {
        $coach = new User();
        $coach->setRawAttributes([
            'id' => 'ffffffff-ffff-4fff-8fff-ffffffffffff',
            'username' => 'coach04',
            'password_hash' => 'not-used',
            'role' => 'coach',
        ]);

        $this->actingAs($coach, 'api')
            ->postJson('/api/v1/loans', [
                'athlete_id' => 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee',
                'items' => [[
                    'equipment_id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
                    'quantity' => 0,
                ]],
            ])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'VALIDATION_ERROR');
    }
}
