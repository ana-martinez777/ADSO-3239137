<?php

namespace Tests\Unit\Domain;

use App\Domain\Inventory\EquipmentInventory;
use App\Exceptions\ApiException;
use PHPUnit\Framework\TestCase;

final class EquipmentInventoryAvailabilityTest extends TestCase
{
    public function test_active_equipment_with_enough_units_can_be_loaned(): void
    {
        EquipmentInventory::assertCanLoan(true, 4, 3);
        self::assertTrue(true);
    }

    public function test_inactive_equipment_cannot_be_loaned(): void
    {
        $this->expectException(ApiException::class);
        EquipmentInventory::assertCanLoan(false, 4, 1);
    }

    public function test_equipment_without_enough_units_cannot_be_loaned(): void
    {
        $this->expectException(ApiException::class);
        EquipmentInventory::assertCanLoan(true, 1, 2);
    }
}
