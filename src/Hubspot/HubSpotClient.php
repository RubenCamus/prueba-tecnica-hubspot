<?php
namespace App\Hubspot;

use App\Http\ApiClient;
use App\Http\ApiResponse;
use App\Validation\ValidationResult;
use Exception;
use Generator;

class HubSpotClient
{
    private const BASE_PATH = 'crm/objects/2026-09/companies';
    private const PAGE_LIMIT = 200;

    private const PROP_NIF = 'nif_cif_vat';
    private const PROP_ESTADO = 'vies_ruben_estado';
    private const PROP_FECHA = 'vies_ruben_fecha';
    private const PROP_MOTIVO = 'vies_ruben_motivo';

    public function __construct(private ApiClient $apiClient)
    {
    }

    public function fetchPendingCompanies(): array
    {
        $body = [
            'filterGroups' => [
                $this->filterGroup(['propertyName' => self::PROP_ESTADO, 'operator' => 'NOT_HAS_PROPERTY']),
                $this->filterGroup(['propertyName' => self::PROP_ESTADO, 'operator' => 'EQ', 'value' => 'ERROR_TEMPORAL']),
            ],
            'properties' => [self::PROP_NIF, self::PROP_ESTADO, self::PROP_FECHA, self::PROP_MOTIVO],
            'sorts' => [['propertyName' => 'hs_object_id', 'direction' => 'ASCENDING']],
        ];

        return iterator_to_array($this->paginate('search', $body), false);
    }

    public function updateViesFields(string $companyId, ValidationResult $result): ApiResponse
    {
        return $this->request('PATCH', $companyId, [
            'properties' => [
                self::PROP_ESTADO => $result->status->value,
                self::PROP_FECHA => $result->date ?? '',
                self::PROP_MOTIVO => $result->reason ?? '',
            ],
        ]);
    }

    private function paginate(string $path, array $body): Generator
    {
        $body['limit'] = self::PAGE_LIMIT;
        $after = null;

        do {
            if ($after !== null) {
                $body['after'] = $after;
            }

            $response = $this->request('POST', $path, $body);

            yield from $response->body['results'] ?? [];

            $after = $response->body['paging']['next']['after'] ?? null;
        } while ($after !== null);
    }

    private function request(string $method, string $path = '', array $body = []): ApiResponse
    {
        $url = rtrim(self::BASE_PATH . '/' . $path, '/');
        $response = $this->apiClient->apiRequest($method, $url, body: $body);

        if ($response->status < 200 || $response->status >= 300) {
            throw new Exception(sprintf(
                'Error en %s %s: %s',
                $method,
                $url,
                $response->error ?? json_encode($response->body)
            ));
        }

        return $response;
    }

    private function filterGroup(array ...$filters): array
    {
        return [
            'filters' => [
                ['propertyName' => self::PROP_NIF, 'operator' => 'HAS_PROPERTY'],
                ...$filters,
            ],
        ];
    }
}
