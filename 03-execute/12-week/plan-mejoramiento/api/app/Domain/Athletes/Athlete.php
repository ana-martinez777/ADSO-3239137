<?php

namespace App\Domain\Athletes;

use App\Exceptions\ApiException;

/** Person who receives equipment on loan. Athletes have no account in the system. */
final class Athlete
{
    private function __construct(
        public readonly string $fullName,
        public readonly string $documentNumber,
    ) {}

    public static function register(string $fullName, string $documentNumber): self
    {
        $fullName = trim(preg_replace('/\s+/u', ' ', $fullName) ?? '');
        $documentNumber = trim($documentNumber);

        if ($fullName === '' || $documentNumber === '') {
            throw new ApiException('VALIDATION_ERROR', 'El nombre completo y el número de documento son obligatorios.', 422);
        }

        return new self($fullName, $documentNumber);
    }

    /** @return array{full_name: string, document_number: string} */
    public function toArray(): array
    {
        return ['full_name' => $this->fullName, 'document_number' => $this->documentNumber];
    }
}
