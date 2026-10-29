<?php
namespace App\Hubspot;

use App\Http\ApiClient;
use Exception;
$config = require __DIR__ . '/../../config.php';
class HubSpotClient {

    public function __construct(private ApiClient $apiClient)
    {}
    // Iterar las companies que no se hayan pasado todavia OR las que hayan dado un ERROR_TEMPORAL
    public function fetchPendingCompanies() {
        $all = [];
        $after = null;
        do {
        $body = [
        'filterGroups' => [
            ['filters' => [
                ['propertyName' => 'nif_cif_vat', 'operator' => 'HAS_PROPERTY'],
                ['propertyName' => 'vies_ruben_estado', 'operator' => 'NOT_HAS_PROPERTY']
            ],
            ],
            ['filters' => [
                ['propertyName' => 'nif_cif_vat', 'operator' => 'HAS_PROPERTY'],
                ['propertyName' => 'vies_ruben_estado', 'operator' => 'EQ', 'value' => 'ERROR_TEMPORAL']
            ],
            ],
        ],
        'limit' => 2,
        'properties' => ['nif_cif_vat', 'vies_ruben_estado','vies_ruben_fecha','vies_ruben_motivo'],
        'sorts' => [['propertyName' => 'hs_object_id', 'direction' => 'ASCENDING']],
        ];
        if ($after!==null) {
            $body['after'] = $after;
        }
        $response = $this->apiClient->apiRequest('POST', 'crm/objects/2026-09/companies/search', body:$body);
        if ($response->status !== 200) {
            throw new Exception("Error al buscar empresas: " . ($response->error ?? json_encode($response->body)));
        }
        $after = $response->body['paging']['next']['after'] ?? null;
        $results = $response->body['results'];
        $all = array_merge($all, $results); // Mergear arrays para mantenerlo todo dentro de 1 mismo array
        } while($after !== null);

        return $all;
    }


}
?>
