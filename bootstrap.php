<?php
require __DIR__ . '/vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

// api key de Hubspot
Dotenv\Dotenv::createImmutable(__DIR__)->load();
$token = $_ENV['SERVICE_KEY'];
if ($token == null or $token =="") {
    throw new Exception("Bootstrap won't load, token not found");
}
// Hacer peticion de crear campos
$nombre = "ruben";
$properties = [
    "vies_{$nombre}_estado" => "VIES " . ucfirst($nombre) . " - Estado",
    "vies_{$nombre}_fecha" => "VIES " . ucfirst($nombre) . " - Fecha validación",
    "vies_{$nombre}_motivo" => "VIES " . ucfirst($nombre) ." - Motivo",
];
// Crear cliente Guzzle
// En Headers pasamos la serviceKey.
// Importante http_errors false para poder gestionar nosotros los error 4xx y 5xx
$hubspotClient = new Client([
    'base_uri' => 'https://api.hubapi.com/',
    'timeout' => 10.0,
    'http_errors' => false,
    'headers' => [
        'Authorization' => "Bearer $token",
        'Accept' => "application/json"
    ]
]);
// Crear Property Group
try {
    $response = $hubspotClient->request('POST', 'crm/properties/2026-09/companies/groups', [
        'json' => ['name' => 'ejercicio_vexco_ruben', 'label' => 'Ejercicio Vexco - Ruben', 'displayOrder' => 123, ],
        'connect_timeout' => 25

    ]);
    echo "StatusCode" . $response->getStatusCode() . PHP_EOL; // Imprimir codigo de respuesta
    echo "Body" . $response->getBody() . PHP_EOL;
} catch (GuzzleException $e) {
    echo "Error en: " . $e->getMessage() . PHP_EOL;
}
?>
