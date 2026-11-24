<?php

namespace Tests\Unit\Domain;

use App\Domain\Inventory\EquipmentInventory;
use App\Exceptions\ApiException;
use PHPUnit\Framework\TestCase;

final class EquipmentInventoryInvalidTotalTest extends TestCase
{
    public function test_total_cannot_be_lower_than_units_on_loan(): void
    {
        $this->expectException(ApiException::class);
        EquipmentInventory::availableAfterTotalChange(2, 3);
    }

    public function test_total_must_be_positive(): void
    {
        $this->expectException(ApiException::class);
        EquipmentInventory::availableAfterTotalChange(0, 0);
    }
}
