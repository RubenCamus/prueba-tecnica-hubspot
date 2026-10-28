<?php
namespace App\Service;

use App\Validation\Status;
use App\Validation\ValidationResult;
use App\Validation\VatNormalizer;
use App\Validation\ViesClassifier;
use App\Vies\ViesClient;

final class CompanyVatProcessor
{
    // Resultados definitivos ya consultados en esta ejecución. Clave: "PAIS:NUMERO"
    private array $cache = [];

    public function __construct(
        private readonly VatNormalizer $normalizer,
        private readonly ViesClient $viesClient,
        private readonly ViesClassifier $classifier,
        private readonly int $delayMicroseconds = 2_000_000, // pausa antes de cada llamada real a VIES
    ) {}

    public function process(?string $rawNif): ValidationResult
    {
        // Normalizar. Si no se puede validar, no se llama a VIES
        $normalized = $this->normalizer->normalize($rawNif);
        if ($normalized->omitReason !== null) {
            return new ValidationResult(status: Status::Omitido, reason: $normalized->omitReason);
        }

        // Caché: mismo NIF ya resuelto en esta ejecución
        $key = $normalized->country . ':' . $normalized->code;
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        // Llamada a VIES, con delay para no saturarlo
        usleep($this->delayMicroseconds);
        $response = $this->viesClient->check($normalized->country, $normalized->code);

        // Clasificar la respuesta
        $result = $this->classifier->classify($response);

        // Solo se guardan los resultados definitivos; un error temporal se reintenta
        if ($result->status !== Status::ErrorTemporal) {
            $this->cache[$key] = $result;
        }
        return $result;
    }
}
