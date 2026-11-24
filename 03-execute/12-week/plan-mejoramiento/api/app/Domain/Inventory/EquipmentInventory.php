<?php

namespace App\Domain\Inventory;

use App\Exceptions\ApiException;

final class EquipmentInventory
{
    public static function availableAfterTotalChange(int $newTotal, int $loanedUnits): int
    {
        if ($newTotal < 1 || $newTotal < $loanedUnits) {
            throw new ApiException(
                'VALIDATION_ERROR',
                'El total no puede ser inferior a las unidades actualmente prestadas.',
                422
            );
        }

        return $newTotal - $loanedUnits;
    }

    public static function assertCanLoan(bool $active, int $available, int $requested): void
    {
        if (!$active) {
            throw new ApiException('EQUIPMENT_INACTIVE', 'El implemento está dado de baja.', 409);
        }

        if ($requested < 1 || $available < $requested) {
            throw new ApiException(
                'EQUIPMENT_NOT_AVAILABLE',
                'No hay unidades disponibles suficientes del implemento solicitado.',
                409
            );
        }
    }
}
