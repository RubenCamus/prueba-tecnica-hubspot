<?php
require __DIR__ . '/vendor/autoload.php';
// Cargar Token
Dotenv\Dotenv::createImmutable(__DIR__)->load();
$token = $_ENV['SERVICE_KEY'] ?? '';
if ($token == null or $token == "") {
    throw new Exception("HUBSPOT request wont work, Service Key token not found");
}
// Declarar Variables

// Configuracion para el ApiClient de Hubspot
$hubspotOptions = [
    'base_uri' => 'https://api.hubapi.com/',
    'timeout' => 10.0,
    'http_errors' => false,
    'headers' => [
        'Authorization' => "Bearer $token",
        'Accept' => "application/json"
    ]
];
return [
    "hubspotOptions" => $hubspotOptions,
];
?>
