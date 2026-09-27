<?php

declare(strict_types = 1);

use QuantumTecnology\FalconDataHub\Auth\TokenManager;
use QuantumTecnology\FalconDataHub\Auth\TokenStore;
use QuantumTecnology\FalconDataHub\FalconConfig;
use QuantumTecnology\FalconDataHub\Resources\Lookup\CepResource;
use QuantumTecnology\FalconDataHub\Resources\Location\StateResource;

/*
|--------------------------------------------------------------------------
| Resources — o caminho completo de uma chamada
|--------------------------------------------------------------------------
|
| Sobe um degrau em relação ao ApiResponseTest: aqui a requisição atravessa o
| resource inteiro (monta URL, injeta o header de autenticação, despacha e
| desempacota). O `FakeHttpClient` entra no lugar do `CurlHttpClient`, então
| nada sai para a rede.
|
| ⚠️ O `FalconClient` instancia o `CurlHttpClient` fixo no construtor e não
| aceita cliente injetado — por isso os testes montam o resource direto, que
| recebe a `HttpClientInterface`. Trocar isso no client seria mudança de API
| pública, e fica como decisão à parte.
|
*/

function makeConfig(string $token = 'tok_123'): FalconConfig
{
    return new FalconConfig(
        baseUrl: 'https://datahub.test',
        token: $token,
    );
}

function makeCepResource(array $responses, ?FalconConfig $config = null): array
{
    $config = $config ?? makeConfig();
    $http   = fakeClient($responses);

    $resource = new CepResource(
        $http,
        new TokenManager($http, $config, new TokenStore()),
        $config,
    );

    return [$resource, $http];
}

it('monta a URL do endpoint a partir da base configurada', function (): void {
    [$cep, $http] = makeCepResource([
        jsonResponse(['success' => true, 'message' => 'OK', 'data' => ['id' => '01001000']]),
    ]);

    $cep->search('01001-000');

    // O SDK sanitiza o CEP antes de montar o caminho — mandar com máscara não
    // pode produzir uma URL diferente.
    expect($http->urlAt(0))->toBe('https://datahub.test/private/v1/cep/01001000/search');
});

it('envia o token no header de autorização', function (): void {
    [$cep, $http] = makeCepResource([
        jsonResponse(['success' => true, 'message' => 'OK', 'data' => []]),
    ]);

    $cep->search('01001000');

    $headers = $http->lastRequest()['headers'];
    $auth    = $headers['Authorization'] ?? $headers['authorization'] ?? null;

    expect($auth)->toBe('Bearer tok_123');
});

it('desempacota a resposta num ApiResponse utilizável', function (): void {
    [$cep] = makeCepResource([
        jsonResponse([
            'success' => true,
            'message' => 'Consulta realizada com sucesso',
            'data'    => ['id' => '01001000', 'city' => 'São Paulo'],
        ]),
    ]);

    $response = $cep->search('01001000');

    expect($response->success)->toBeTrue()
        ->and($response->data['city'])->toBe('São Paulo');
});

it('entrega a paginação do bloco `pagination` a quem lista', function (): void {
    // O caso ponta a ponta do bug corrigido: não basta o ApiResponse saber ler
    // — o consumidor precisa receber `meta` preenchido ao chamar o resource.
    $config = makeConfig();
    $http   = fakeClient([
        jsonResponse([
            'success'    => true,
            'message'    => 'OK',
            'data'       => [['id' => 1], ['id' => 2]],
            'pagination' => [
                'current_page' => 1,
                'last_page'    => 3,
                'per_page'     => 15,
                'total'        => 45,
            ],
        ]),
    ]);

    $states = new StateResource(
        $http,
        new TokenManager($http, $config, new TokenStore()),
        $config,
    );

    $response = $states->list();

    expect($response->meta['total'])->toBe(45)
        ->and($response->meta['last_page'])->toBe(3)
        ->and($response->data)->toHaveCount(2);
});

it('dispara UMA requisição por chamada', function (): void {
    // Guarda contra retry acidental: o DataHub cobra por requisição, então uma
    // chamada que vire duas dobra a conta do cliente em silêncio.
    [$cep, $http] = makeCepResource([
        jsonResponse(['success' => true, 'message' => 'OK', 'data' => []]),
    ]);

    $cep->search('01001000');

    expect($http->callCount())->toBe(1);
});

it('mantém as letras do CNPJ alfanumérico na URL da consulta (NT 2026.004)', function (): void {
    $config = makeConfig();
    $http   = fakeClient([
        jsonResponse(['success' => true, 'message' => 'OK', 'data' => []]),
    ]);

    $resource = new QuantumTecnology\FalconDataHub\Resources\Lookup\CnpjResource(
        $http,
        new TokenManager($http, $config, new TokenStore()),
        $config,
    );

    $resource->search('12.abc.345/01de-35');

    // Com sanitizeDigits a URL ia com "123450135" — outro CNPJ, truncado.
    expect($http->urlAt(0))->toBe('https://datahub.test/private/v1/cnpj/12ABC34501DE35/search');
});
