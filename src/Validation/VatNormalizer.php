<?php

namespace App\Validation;
use App\Validation\NormalizedVat;

class VatNormalizer {
    private const ALLOWED_COUNTRIES = [
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'EL', 'ES',
        'FI', 'FR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT',
        'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK', 'XI',
    ];
    private const REASON_EMPTY = "Nif is empty";
    private const REASON_PREFIX_INVALID = "Prefix is not valid";
    private const REASON_CODE_LENGTH_INVALID = "Length code is not valid";

    public function normalize(?string $raw): NormalizedVat {
        // Mayúsculas y quitar caracteres inválidos. Con null o solo basura devuelve ''
        $clean = $this->clean($raw);
        if ($clean === '') {
            return new NormalizedVat(omitReason: self::REASON_EMPTY);
        }

        // País ya convertido (GR -> EL) y validado. null si no es válido
        $country = $this->resolveCountry($clean);
        if ($country === null) {
            return new NormalizedVat(omitReason: self::REASON_PREFIX_INVALID);
        }

        // Número sin el prefijo
        $number = $this->extractNumber($clean);
        if (!$this->isNumberLengthValid($number)) {
            return new NormalizedVat(omitReason: self::REASON_CODE_LENGTH_INVALID);
        }

        return new NormalizedVat(country: $country, code: $number);
    }

    // Mayúsculas y eliminar todo lo que no sea A-Z o 0-9
    private function clean(?string $raw): string {
        $upper = mb_strtoupper($raw ?? '');
        return preg_replace('/[^A-Z0-9]/u', '', $upper) ?? '';
    }

    // Devuelve el código de país listo para VIES, o null si el prefijo no es válido
    private function resolveCountry(string $clean): ?string {
        $prefix = substr($clean, 0, 2);
        if ($prefix === 'GR') {$prefix = "EL";} // El código de Grecia en VIES es EL
        return in_array($prefix, self::ALLOWED_COUNTRIES, true) ? $prefix : null;
    }

    // Todo lo que va después del prefijo (los 2 primeros caracteres)
    private function extractNumber(string $clean): string {
        return substr($clean, 2);
    }

    private function isNumberLengthValid(string $number): bool {
        return strlen($number) >= 2 && strlen($number) <= 12;
    }
}
