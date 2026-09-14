<?php

declare(strict_types = 1);

use QuantumTecnology\FalconDataHub\Http\HttpResponse;
use QuantumTecnology\FalconDataHub\Tests\Support\FakeHttpClient;

/*
|--------------------------------------------------------------------------
| Suíte do SDK
|--------------------------------------------------------------------------
|
| Nenhum teste sai para a rede. O SDK usa cURL direto (não o HTTP client do
| Laravel), então não há `Http::fake()` — o que existe, e é melhor, é a
| `HttpClientInterface`: o `FalconClient` aceita um cliente injetado, e a
| suíte passa um `FakeHttpClient` que devolve respostas combinadas e grava o
| que foi pedido.
|
*/

/**
 * Resposta pronta para o FakeHttpClient.
 *
 * @param array<string, mixed>|list<mixed> $body
 */
function jsonResponse(array $body, int $status = 200): HttpResponse
{
    return new HttpResponse(
        statusCode: $status,
        body: json_encode($body, JSON_THROW_ON_ERROR),
        headers: ['content-type' => 'application/json'],
    );
}

/**
 * Cliente falso com uma fila de respostas.
 *
 * @param list<HttpResponse> $responses
 */
function fakeClient(array $responses = []): FakeHttpClient
{
    return new FakeHttpClient($responses);
}
