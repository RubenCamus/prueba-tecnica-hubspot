<?php
namespace App\Vies;

use App\Http\ApiClient;
use App\Http\ApiResponse;

readonly class ViesClient {

    public function __construct(private ApiClient $apiClient)
    {}
    public function check(string $country, string $code): ApiResponse {
        return $this->apiClient->apiRequest('POST', 'check-vat-number', body:
        [
            "countryCode" => $country,
            "vatNumber" => $code
        ]);
    }

}
