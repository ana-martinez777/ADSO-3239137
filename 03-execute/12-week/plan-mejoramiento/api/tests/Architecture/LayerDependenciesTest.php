<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

/** constitution.md, articles I and II: dependencies point inwards. */
final class LayerDependenciesTest extends TestCase
{
    private const FORBIDDEN_IN_DOMAIN = ['Illuminate\\', 'Carbon\\', 'App\\Application\\', 'App\\Infrastructure\\', 'App\\Models\\', 'App\\Http\\', 'App\\Services\\'];
    private const FORBIDDEN_IN_APPLICATION = ['Illuminate\\', 'App\\Infrastructure\\', 'App\\Models\\', 'App\\Http\\', 'App\\Services\\'];

    public function test_domain_does_not_depend_on_framework_or_outer_layers(): void
    {
        $this->assertLayerIsIndependent('Domain', self::FORBIDDEN_IN_DOMAIN);
    }

    public function test_application_depends_only_on_domain_and_its_ports(): void
    {
        $this->assertLayerIsIndependent('Application', self::FORBIDDEN_IN_APPLICATION);
    }

    public function test_application_ports_are_interfaces(): void
    {
        foreach ($this->phpFiles('Application/Ports') as $file) {
            self::assertMatchesRegularExpression('/^interface\s+\w+/m', file_get_contents($file), basename($file).' must be an interface.');
        }
    }

    private function assertLayerIsIndependent(string $layer, array $forbidden): void
    {
        $files = $this->phpFiles($layer);
        self::assertNotEmpty($files, "Layer {$layer} has no files.");

        foreach ($files as $file) {
            preg_match_all('/^use\s+([^;]+);/m', file_get_contents($file), $matches);
            foreach ($matches[1] as $import) {
                foreach ($forbidden as $prefix) {
                    self::assertStringStartsNotWith($prefix, $import, sprintf('%s imports %s', basename($file), $import));
                }
            }
        }
    }

    /** @return string[] */
    private function phpFiles(string $relativePath): array
    {
        $directory = dirname(__DIR__, 2).'/app/'.$relativePath;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));
        $files = [];
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        sort($files);

        return $files;
    }
}
