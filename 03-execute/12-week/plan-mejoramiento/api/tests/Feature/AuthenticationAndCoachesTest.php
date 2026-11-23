<?php
namespace Tests\Feature;

use App\Application\Ports\PasswordHasher;
use App\Application\Ports\TokenIssuer;
use App\Application\Ports\UserRepository;
use App\Domain\Users\UserAccount;
use App\Models\User;
use Tests\TestCase;

class AuthenticationAndCoachesTest extends TestCase
{
    public function test_login_returns_contract_response(): void
    {
        $this->bindLoginFakes(true);

        $this->postJson('/api/v1/auth/login', [
            'username' => ' ADMIN ',
            'password' => 'correct-password',
        ])->assertOk()
          ->assertJsonPath('data.access_token', 'fake-jwt-token')
          ->assertJsonPath('data.token_type', 'Bearer')
          ->assertJsonPath('data.expires_in', 28800)
          ->assertJsonPath('data.user.username', 'admin')
          ->assertJsonPath('data.user.role', 'admin')
          ->assertJsonMissingPath('data.user.password_hash');
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $this->bindLoginFakes(false);

        $this->postJson('/api/v1/auth/login', [
            'username' => 'admin',
            'password' => 'wrong-password',
        ])->assertUnauthorized()
          ->assertJsonPath('code', 'INVALID_CREDENTIALS')
          ->assertJsonStructure(['code', 'message', 'details']);
    }

    public function test_login_validation_uses_uniform_error_response(): void
    {
        $this->bindLoginFakes(true);

        $this->postJson('/api/v1/auth/login', [])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['code', 'message', 'details']);
    }

    public function test_coach_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/users/coaches')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'UNAUTHENTICATED');
    }

    public function test_coach_role_cannot_access_admin_endpoints(): void
    {
        $coach = new User();
        $coach->setRawAttributes([
            'id' => '11111111-1111-4111-8111-111111111111',
            'username' => 'coach01',
            'password_hash' => 'unused',
            'role' => 'coach',
        ]);

        $this->actingAs($coach, 'api')
            ->getJson('/api/v1/users/coaches')
            ->assertForbidden()
            ->assertJsonPath('code', 'FORBIDDEN_ROLE');
    }

    public function test_admin_can_register_coach_with_normalized_username(): void
    {
        $this->app->instance(UserRepository::class, new class implements UserRepository {
            public function findByUsername(string $username): ?UserAccount { return null; }
            public function createCoach(string $username, string $passwordHash): UserAccount
            {
                return new UserAccount('22222222-2222-4222-8222-222222222222', $username, 'hash', 'coach');
            }
            public function findCoaches(?string $search = null): array { return []; }
        });

        $admin = new User();
        $admin->setRawAttributes([
            'id' => '33333333-3333-4333-8333-333333333333',
            'username' => 'admin',
            'password_hash' => 'unused',
            'role' => 'admin',
        ]);

        $this->actingAs($admin, 'api')
            ->postJson('/api/v1/users/coaches', [
                'username' => '  CoachOne  ',
                'password' => 'temporary-password',
            ])
            ->assertCreated()
            ->assertJsonPath('data.username', 'coachone')
            ->assertJsonPath('data.role', 'coach')
            ->assertJsonMissingPath('data.password_hash');
    }

    private function bindLoginFakes(bool $passwordIsValid): void
    {
        $account = new UserAccount(
            '44444444-4444-4444-8444-444444444444',
            'admin',
            'fake-hash',
            'admin'
        );

        $this->app->instance(UserRepository::class, new class($account) implements UserRepository {
            public function __construct(private UserAccount $account) {}
            public function findByUsername(string $username): ?UserAccount
            {
                return $username === 'admin' ? $this->account : null;
            }
            public function createCoach(string $username, string $passwordHash): UserAccount
            {
                throw new \LogicException('No se utiliza en esta prueba.');
            }
            public function findCoaches(?string $search = null): array { return []; }
        });

        $this->app->instance(PasswordHasher::class, new class($passwordIsValid) implements PasswordHasher {
            public function __construct(private bool $valid) {}
            public function verify(string $plainPassword, string $passwordHash): bool
            {
                return $this->valid && $plainPassword === 'correct-password';
            }
            public function hash(string $plainPassword): string { return 'fake-hash'; }
        });

        $this->app->instance(TokenIssuer::class, new class implements TokenIssuer {
            public function generate(UserAccount $user): string { return 'fake-jwt-token'; }
        });
    }
}
