<?php

namespace Tests\Feature;

use App\Application\Ports\SportsGearRepository;
use App\Models\User;
use Mockery;
use RuntimeException;
use Tests\TestCase;

/** RNF-04: los errores no controlados mantienen el formato uniforme y no exponen datos internos. */
final class ApiErrorHandlingTest extends TestCase
{
    public function test_unexpected_errors_return_uniform_body_without_internal_details(): void
    {
        $repository = Mockery::mock(SportsGearRepository::class);
        $repository->shouldReceive('categories')
            ->andThrow(new RuntimeException('SQLSTATE[HY000] select * from `users` where password_hash'));
        $this->app->instance(SportsGearRepository::class, $repository);

        $response = $this->actingAs($this->admin(), 'api')->getJson('/api/v1/categories');

        $response->assertStatus(500)
            ->assertExactJson([
                'code' => 'INTERNAL_ERROR',
                'message' => 'Ocurrió un error interno. Intenta nuevamente más tarde.',
                'details' => [],
            ]);
        $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
        $this->assertStringNotContainsString('select', $response->getContent());
    }

    public function test_unknown_api_route_returns_not_found_code(): void
    {
        $this->getJson('/api/v1/no-existe')
            ->assertNotFound()
            ->assertExactJson([
                'code' => 'NOT_FOUND',
                'message' => 'El recurso solicitado no existe.',
                'details' => [],
            ]);
    }

    public function test_unsupported_http_method_keeps_uniform_body(): void
    {
        $this->deleteJson('/api/v1/health')
            ->assertStatus(405)
            ->assertJsonPath('code', 'HTTP_ERROR')
            ->assertJsonStructure(['code', 'message', 'details']);
    }

    private function admin(): User
    {
        $user = new User();
        $user->setRawAttributes([
            'id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'username' => 'admin',
            'role' => 'admin',
        ], true);

        return $user;
    }
}
