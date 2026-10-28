<?php
namespace App\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ApiClient {

    // Creacion de Cliente - Mas facil para crear 1 para Hubspot y otro para VIES
    private readonly Client $client;
    private const RETRY_ATTEMPTS = 4;
    private const MAX_WAIT_SECONDS = 20;
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    // Peticion Generica de la API - Metodo(GET/Post...), ruta a la que hacer peticion, body de la request
    public function apiRequest(string $requestMethod, string $requestRoute, ?array $body = null, ?array $query = null): ApiResponse {
        try {
            // Si el body esta vacio no se envia nada en la request
            $options = []; // Array asociativo con los parametros del body
            if ($body !== null) {
                $options['json'] = $body;
            }
            if ($query !== null) {
                $options['query'] = $query;
            }
            $response = $this->client->request($requestMethod,$requestRoute, $options);
            $attempt = 0;
            while($response->getStatusCode() === 429 && $attempt < self::RETRY_ATTEMPTS) {
                $header = $response->getHeaderLine('Retry-After');
                $waitTime = is_numeric($header) ? (int) $header : 2 ** $attempt;
                sleep(min($waitTime, self::MAX_WAIT_SECONDS));
                $response = $this->client->request($requestMethod,$requestRoute,$options);
                $attempt++;
            }
            $textoBody = (string) $response->getBody();
            // Devuelve lo siguiente: Codigo: 200,404,500... | El body devuelve un array asociativo $respuesta->body["campo"]
            return new ApiResponse(status: $response->getStatusCode(), body: json_decode($textoBody, true));
        } catch (GuzzleException $e) {
            return new ApiResponse(error: $e->getMessage());
        }
    }}
