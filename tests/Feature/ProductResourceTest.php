<?php

declare(strict_types = 1);

use QuantumTecnology\FalconDataHub\Auth\TokenManager;
use QuantumTecnology\FalconDataHub\Auth\TokenStore;
use QuantumTecnology\FalconDataHub\FalconConfig;
use QuantumTecnology\FalconDataHub\Resources\Products\ProductResource;

/*
|--------------------------------------------------------------------------
| Produtos — filtro por loja e período do histórico (1.9.0)
|--------------------------------------------------------------------------
|
| A API lê `filter[store_id]`, `filter[observed_from]` e `filter[observed_to]`.
| O que importa aqui é a forma da query: um filtro com nome errado não dá
| erro na API, é só ignorado, e a resposta volta sem filtro nenhum.
|
*/

function makeProductResource(): array
{
    // Config montada aqui, não via `makeConfig()`: aquele helper mora no
    // ResourceRequestTest e só existe quando a suíte inteira roda.
    $config = new FalconConfig(baseUrl: 'https://datahub.test', token: 'tok_123');
    $http   = fakeClient([jsonResponse(['data' => []])]);

    return [new ProductResource($http, new TokenManager($http, $config, new TokenStore()), $config), $http];
}

it('busca sem loja manda só o termo', function (): void {
    [$products, $http] = makeProductResource();

    $products->search('leite');

    expect($http->lastRequest()['payload'])->toBe(['q' => 'leite']);
});

it('busca filtrada por uma loja', function (): void {
    [$products, $http] = makeProductResource();

    $products->search('leite', storeIds: 'Ke7wJd3NxF');

    expect($http->lastRequest()['payload'])->toBe([
        'q'      => 'leite',
        'filter' => ['store_id' => 'Ke7wJd3NxF'],
    ]);
});

it('busca filtrada por várias lojas junta com vírgula', function (): void {
    [$products, $http] = makeProductResource();

    $products->search('leite', storeIds: ['Ke7wJd3NxF', 'Yr2gPb5LhC']);

    expect($http->lastRequest()['payload']['filter'])->toBe(['store_id' => 'Ke7wJd3NxF,Yr2gPb5LhC']);
});

it('histórico aceita o id em hashid', function (): void {
    [$products, $http] = makeProductResource();

    $products->prices('v5rNVenmKP');

    expect($http->urlAt(0))->toBe('https://datahub.test/private/v1/products/v5rNVenmKP/prices')
        ->and($http->lastRequest()['payload'])->toBe([]);
});

it('histórico com região e período', function (): void {
    [$products, $http] = makeProductResource();

    $products->prices('v5rNVenmKP', region: 'Sorocaba', observedFrom: '2026-03-01', observedTo: '2026-03-31');

    expect($http->lastRequest()['payload'])->toBe([
        'region' => 'Sorocaba',
        'filter' => ['observed_from' => '2026-03-01', 'observed_to' => '2026-03-31'],
    ]);
});

it('histórico só "até" não manda o início', function (): void {
    [$products, $http] = makeProductResource();

    $products->prices(42, observedTo: '2026-03-21');

    expect($http->lastRequest()['payload'])->toBe(['filter' => ['observed_to' => '2026-03-21']]);
});
