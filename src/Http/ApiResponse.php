<?php
namespace App\Http;

final readonly class ApiResponse {
    public function __construct(
    public ?int $status = null,
    public ?array $body = null,
    public ?string $error = null)
    {}
}
