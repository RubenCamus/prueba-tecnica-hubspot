<?php

namespace App\Validation;

final readonly class ValidationResult {
    public function __construct(
    public Status $status,
    public ?string $date = null,
    public ?string $reason = null,
    )
    {}
}
