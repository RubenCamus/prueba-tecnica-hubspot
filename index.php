<?php
use App\Http\ApiClient;
use App\Hubspot\HubSpotClient;
use App\Service\CompanyVatProcessor;
use App\Validation\VatNormalizer;
use App\Validation\ViesClassifier;
use App\Vies\ViesClient;
use GuzzleHttp\Client;
$config = require __DIR__ . '/config.php';

// Instanciar todas las clases y dependencias
$apiClientHubspot = new ApiClient(new Client($config['viesOptions']));
$apiClientVies = new ApiClient(new Client($config['hubspotOptions']));
$hubspotClient = new HubSpotClient($apiClientHubspot);
$viesClient = new ViesClient($apiClientVies);
$vatNormalizer = new VatNormalizer();
$viesClassifier = new ViesClassifier();
$companyVatProcessor = new CompanyVatProcessor($vatNormalizer, $viesClient, $viesClassifier);

// Llamar API hubspot para sacar empresas sin clasificar
$unprocessedCompanies = $hubspotClient->fetchPendingCompanies();
foreach($unprocessedCompanies as $companyToProcess) {
    // Validar NIF y llamar a VIES. Devuelve un ValidationResult (Estado, Fecha, Motivo )
    $validationResult = $companyVatProcessor->process($companyToProcess);
    //
}
?>
