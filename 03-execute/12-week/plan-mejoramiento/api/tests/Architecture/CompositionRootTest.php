<?php

namespace Tests\Architecture;

use App\Application\Ports\Clock;
use App\Application\Ports\PasswordHasher;
use App\Application\Ports\SportsGearRepository;
use App\Application\Ports\TokenIssuer;
use App\Application\Ports\UserRepository;
use App\Infrastructure\Persistence\EloquentSportsGearRepository;
use App\Infrastructure\Persistence\EloquentUserRepository;
use App\Infrastructure\Security\LaravelPasswordHasher;
use App\Infrastructure\Time\SystemClock;
use App\Services\Auth\JwtService;
use Tests\TestCase;

/** constitution.md, article III: one composition point connects ports and adapters. */
final class CompositionRootTest extends TestCase
{
    public function test_every_port_resolves_to_its_adapter(): void
    {
        $expected = [
            UserRepository::class => EloquentUserRepository::class,
            SportsGearRepository::class => EloquentSportsGearRepository::class,
            PasswordHasher::class => LaravelPasswordHasher::class,
            TokenIssuer::class => JwtService::class,
            Clock::class => SystemClock::class,
        ];

        foreach ($expected as $port => $adapter) {
            self::assertInstanceOf($adapter, $this->app->make($port));
        }
    }

    public function test_ports_are_bound_only_in_the_app_service_provider(): void
    {
        $root = dirname(__DIR__, 2);
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root.'/app', \FilesystemIterator::SKIP_DOTS));

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php' || $file->getFilename() === 'AppServiceProvider.php') {
                continue;
            }
            self::assertDoesNotMatchRegularExpression('/->(bind|singleton)\(/', file_get_contents($file->getPathname()), $file->getFilename().' binds a dependency.');
        }
    }

    public function test_system_clock_returns_utc(): void
    {
        self::assertSame('UTC', $this->app->make(Clock::class)->now()->getTimezone()->getName());
    }
}
