<?php

namespace App\Validation;

final readonly class NormalizedVat {
    public function __construct(
    public ?string $country = null,
    public ?string $code = null,
    public ?string $omitReason = null
    )
    {}
}
