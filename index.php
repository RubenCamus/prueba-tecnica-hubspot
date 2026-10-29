<?php
use App\Http\ApiClient;
use App\Hubspot\HubSpotClient;
use App\Services\CompanyVatProcessor;
use App\Services\Summary;
use App\Validation\VatNormalizer;
use App\Validation\ViesClassifier;
use App\Vies\ViesClient;
use GuzzleHttp\Client;
$config = require __DIR__ . '/config.php';
$dryRun = in_array('--dry-run', $argv, true);
if ($dryRun === true) {
    echo "Modo de prueba ON".PHP_EOL;
}
// Instanciar todas las clases y dependencias
$apiClientHubspot = new ApiClient(new Client($config['hubspotOptions']));
$apiClientVies = new ApiClient(new Client($config['viesOptions']));
$hubspotClient = new HubSpotClient($apiClientHubspot);
$viesClient = new ViesClient($apiClientVies);
$vatNormalizer = new VatNormalizer();
$viesClassifier = new ViesClassifier();
$companyVatProcessor = new CompanyVatProcessor($vatNormalizer, $viesClient, $viesClassifier);
$summary = new Summary();
// Llamar API hubspot para sacar empresas sin clasificar
try {
    $unprocessedCompanies = $hubspotClient->fetchPendingCompanies();
} catch( Exception $e) {
    // Si el fetch inicial de companies falla se para la ejecucion
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}
foreach($unprocessedCompanies as $companyToProcess) {
    // Guardar ID y NIF de la empresa
    $companyNif = $companyToProcess['properties']['nif_cif_vat'] ;
    $companyId = $companyToProcess['id'] ?? null;
    // Validar NIF y llamar a VIES. Devuelve un ValidationResult (Estado, Fecha, Motivo )
    $validationResult = $companyVatProcessor->process($companyNif);
    // Si $dryRun esta habilitado no escribe en Hubspot, solo hace la prueba
    if ($dryRun !== true) {
        // Escribir en Hubspot
        try {
            $hubspotClient->updateViesFields($companyId, $validationResult);
        } catch (Exception $e) {
            $summary->failCounter++;
            echo $e->getMessage() . " ". $companyId .PHP_EOL;
        }
    }
    echo ("ID empresa: " . $companyId . " NIF: " . $companyNif . " ". $validationResult->status->value) . PHP_EOL;
    $summary->add($validationResult);
}
echo $summary->render() . PHP_EOL;
$logsRoute = __DIR__ . '/logs';
if (!is_dir($logsRoute)) {
    mkdir($logsRoute);
}
if ($dryRun) {
    file_put_contents($logsRoute."/INFORME-PRUEBA-" .date('Y-m-d_H-i') . '.txt', $summary->render());
}else {file_put_contents($logsRoute."/INFORME-" .date('Y-m-d_H-i') . '.txt', $summary->render());}
?>
