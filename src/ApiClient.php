<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ApiClient {

    // Creacion de Cliente - Mas facil para crear 1 para Hubspot y otro para VIES
    private readonly Client $client;
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    // Peticion Generica de la API - Metodo(GET/Post...), ruta a la que hacer peticion, body de la request
    public function apiRequest(string $requestMethod, string $requestRoute, ?array $body = null): ApiResponse {
        try {
            // Si el body esta vacio no se envia nada en la request
            $options = []; // Array asociativo con los parametros del body
            if ($body !== null) {
                $options['json'] = $body;
            }
            $response = $this->client->request($requestMethod,$requestRoute, $options);
            $textoBody = (string) $response->getBody();
            return new ApiResponse(status: $response->getStatusCode(), body: json_decode($textoBody, true));
        } catch (GuzzleException $e) {
            return new ApiResponse(error: $e->getMessage());
        }
    }}

?>
