<?php
namespace App\Validation;

use App\Http\ApiResponse;
use App\Validation\Status;
use App\Validation\ValidationResult;

class ViesClassifier {
    public function classify(ApiResponse $response): ValidationResult {
        // Error NOT NULL => Asignar Error Temporal
        if ($response->error !== null) {
            return new ValidationResult(status: Status::ErrorTemporal, reason: $response->error);
        }
        $valid = $response->body['valid'] ?? null;
        $date = $response->body['requestDate'] ?? null;
        if ($valid === true) {
            return new ValidationResult(status: Status::Valido, date: $date);
        }
        if ($valid === false) {
            return new ValidationResult(status: Status::Invalido, date: $date, reason: "nif_cif_vat inválido para VIES");
        }
        $errorCode = $response->body['errorWrappers'][0]['error'] ?? null;
        if ($errorCode === "INVALID_INPUT") {
            return new ValidationResult(status: Status::Omitido, reason: $errorCode);
        }
        if ($errorCode  !== null) {
            return new ValidationResult(status: Status::ErrorTemporal, reason: $errorCode);
        }
        return new ValidationResult(status: Status::ErrorTemporal, reason: "Unexpected response from VIES. HTTP: " . $response->status);
    }
}
