<?php
namespace App\Infrastructure\Persistence;

use App\Application\Ports\PasswordHasher;
use App\Application\Ports\UserRepository;
use App\Domain\Users\UserAccount;
use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Database\QueryException;

final class EloquentUserRepository implements UserRepository
{
    public function __construct(private readonly PasswordHasher $passwords) {}

    public function findByUsername(string $username): ?UserAccount
    {
        $user = User::query()->where('username', $username)->first();
        return $user ? $this->toAccount($user) : null;
    }

    public function createCoach(string $username, string $passwordHash): UserAccount
    {
        try {
            // The adapter creates the hash; the model receives it already computed.
            $user = User::query()->create([
                'username' => $username,
                'password_hash' => $this->passwords->hash($passwordHash),
                'role' => 'coach',
            ]);
        } catch (QueryException $exception) {
            // The UNIQUE constraint also protects against concurrent registrations.
            if (($exception->errorInfo[0] ?? null) === '23000') {
                throw new ApiException('USERNAME_ALREADY_EXISTS', 'El nombre de usuario ya está registrado.', 409);
            }
            throw $exception;
        }

        return $this->toAccount($user);
    }

    public function findCoaches(?string $search = null): array
    {
        $query = User::query()->where('role', 'coach')->orderBy('username');
        if ($search !== null) {
            $query->where('username', 'like', '%' . $search . '%');
        }

        return $query->get()
            ->map(fn (User $user): UserAccount => $this->toAccount($user))
            ->all();
    }

    private function toAccount(User $user): UserAccount
    {
        return new UserAccount(
            (string) $user->id,
            (string) $user->username,
            (string) $user->password_hash,
            (string) $user->role,
        );
    }
}
