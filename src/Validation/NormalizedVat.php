<?php

namespace App\Validation;

final class NormalizedVat {
    public function __construct(
    public readonly ?string $country = null,
    public readonly ?string $code = null,
    public readonly ?string $omitReason = null
    )
    {}
}
