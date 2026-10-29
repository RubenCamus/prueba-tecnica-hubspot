<?php

namespace App\Services;

use App\Validation\Status;
use App\Validation\ValidationResult;

class Summary  {
    private array $valueCounters = [
        Status::Valido->value => 0,
        Status::Invalido->value => 0,
        Status::Omitido->value => 0,
        Status::ErrorTemporal->value => 0
    ];
    public function add(ValidationResult $result): void {
        $this->valueCounters[$result->status->value]++;
    }
    public function total(): int {
        // Sumar todos los contadores y devolver el total
        $total = 0;
        foreach($this->valueCounters as $counter) {
            $total = $total + $counter;
        }
        return $total;
    }
    public function render(): string
    {
        $text = "Resumen" . PHP_EOL;
        foreach ($this->valueCounters as $status => $count) {
            $text .= $status . ": " . $count . PHP_EOL;
        }
        // Anadir el total
        $text .= "TOTAL: " . $this->total() . PHP_EOL;
        return $text;
    }
}
