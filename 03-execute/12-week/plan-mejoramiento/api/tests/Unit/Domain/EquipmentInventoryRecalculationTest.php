<?php

namespace Tests\Unit\Domain;

use App\Domain\Inventory\EquipmentInventory;
use PHPUnit\Framework\TestCase;

final class EquipmentInventoryRecalculationTest extends TestCase
{
    public function test_available_units_are_recalculated_preserving_loaned_units(): void
    {
        self::assertSame(9, EquipmentInventory::availableAfterTotalChange(12, 3));
    }
}
