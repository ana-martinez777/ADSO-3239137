<?php

namespace App\Domain\Catalog;

/** The five fixed catalog categories, created by the initial seeding. */
final class Category
{
    public const NAMES = ['Balones', 'Raquetas', 'Protección', 'Atletismo', 'Gimnasio'];

    public static function exists(string $name): bool
    {
        return in_array($name, self::NAMES, true);
    }
}
