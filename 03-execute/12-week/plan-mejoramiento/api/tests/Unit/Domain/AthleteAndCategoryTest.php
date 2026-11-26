<?php

namespace Tests\Unit\Domain;

use App\Domain\Athletes\Athlete;
use App\Domain\Catalog\Category;
use App\Exceptions\ApiException;
use PHPUnit\Framework\TestCase;

final class AthleteAndCategoryTest extends TestCase
{
    public function test_athlete_data_is_normalized(): void
    {
        $athlete = Athlete::register('  Laura   Gómez ', ' 1075 ');
        self::assertSame(['full_name' => 'Laura Gómez', 'document_number' => '1075'], $athlete->toArray());
    }

    public function test_athlete_requires_name_and_document(): void
    {
        $this->expectException(ApiException::class);
        Athlete::register(' ', '1075');
    }

    public function test_catalog_has_the_five_fixed_categories(): void
    {
        self::assertSame(['Balones', 'Raquetas', 'Protección', 'Atletismo', 'Gimnasio'], Category::NAMES);
        self::assertTrue(Category::exists('Gimnasio'));
        self::assertFalse(Category::exists('Natación'));
    }
}
