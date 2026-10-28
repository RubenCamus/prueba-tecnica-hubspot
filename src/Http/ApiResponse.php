<?php
namespace App\Http;

class ApiResponse {
    public function __construct(
    public readonly ?int $status = null,
    public readonly ?array $body = null,
    public readonly ?string $error = null)
    {}
}
