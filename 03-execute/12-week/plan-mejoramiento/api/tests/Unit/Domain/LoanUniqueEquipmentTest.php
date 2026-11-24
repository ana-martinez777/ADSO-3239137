<?php

namespace Tests\Unit\Domain;

use App\Domain\Loans\LoanPolicy;
use App\Exceptions\ApiException;
use PHPUnit\Framework\TestCase;

final class LoanUniqueEquipmentTest extends TestCase
{
    public function test_same_equipment_cannot_be_repeated_in_one_loan(): void
    {
        $this->expectException(ApiException::class);
        LoanPolicy::assertUniqueEquipment([
            ['equipment_id' => 'a'],
            ['equipment_id' => 'a'],
        ]);
    }

    public function test_different_equipment_ids_are_allowed(): void
    {
        LoanPolicy::assertUniqueEquipment([
            ['equipment_id' => 'a'],
            ['equipment_id' => 'b'],
        ]);
        self::assertTrue(true);
    }
}
