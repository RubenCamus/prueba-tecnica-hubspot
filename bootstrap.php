<?php
require __DIR__ . '/vendor/autoload.php';
use App\Http\ApiClient;
use App\Http\ApiResponse;
$config = require __DIR__ . '/config.php';
use GuzzleHttp\Client;

// CONFIGURACION
// Api Key Hubspot
Dotenv\Dotenv::createImmutable(__DIR__)->load();
$token = $_ENV['SERVICE_KEY'];
if ($token == null or $token =="") {
    throw new Exception("Bootstrap won't load, token not found");
}

// Nombres Propiedades
const USER_NAME = "ruben";
const PROPERTY_GROUP = "ejercicio_vexco_ruben";
$displayName = ucfirst(USER_NAME);
$properties = [
    'vies_' . USER_NAME . '_estado' => "VIES $displayName - Estado",
    'vies_' . USER_NAME . '_fecha'  => "VIES $displayName - Fecha validación",
    'vies_' . USER_NAME . '_motivo' => "VIES $displayName - Motivo",
];

// FUNCIONES
function handleResponse(ApiResponse $response) {
    if ($response->error !== null) {
        return 'error';
    }
    if ($response->status === 201) {
        return 'created';
    }
    if ($response->status === 409) {
        return 'exists';
    }
    return 'error';
}

// FLUJO EJECUCION

$apiClient = new ApiClient(new Client($config['hubspotOptions']));

// CREAR Grupo de Propiedades
$groupResponse = $apiClient->apiRequest('POST', 'crm/properties/2026-09/companies/groups',['name' => PROPERTY_GROUP, 'label' => 'Ejercicio Vexco - Ruben', 'displayOrder' => 123,]);
$groupResponseStatus = handleResponse($groupResponse);
if ($groupResponseStatus == 'error') {
    echo "ERROR: Property Group was not created " . PHP_EOL;
    exit(1);
};
// TODO Instanciar Logger para guardar respuesta de crear property group

// Crear propierties
foreach ($properties as $name => $label) {
    $createPropertyResponse = $apiClient->apiRequest(
    'POST',
    'crm/properties/2026-09/companies',
    [
        'name' => $name,
        'label' => $label,
        'fieldType'=> 'text',
        'groupName' => PROPERTY_GROUP,
        'type' => 'string'
    ]);
    switch (handleResponse($createPropertyResponse)) {
        case 'created':
        echo "CREADA: $name" . PHP_EOL;
        break;
        case 'exists':
        echo "EXISTE: $name" . PHP_EOL;
        break;
        default:
        echo "ERROR: $name " . $createPropertyResponse->error . PHP_EOL;
        exit(1);
    }
}

// TODO Instanciar Logger para guardar respuesta de creacion de propiedades
?>
