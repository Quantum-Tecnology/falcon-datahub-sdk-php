<?php

declare(strict_types = 1);

use QuantumTecnology\FalconDataHub\Auth\TokenManager;
use QuantumTecnology\FalconDataHub\Auth\TokenStore;
use QuantumTecnology\FalconDataHub\Exceptions\FalconException;
use QuantumTecnology\FalconDataHub\Exceptions\ForbiddenException;
use QuantumTecnology\FalconDataHub\Resources\ApiKeys\ApiKeyResource;
use QuantumTecnology\FalconDataHub\Resources\IntegrationTokens\IntegrationTokenResource;
use QuantumTecnology\FalconDataHub\Resources\Lookup\CepResource;

/*
|--------------------------------------------------------------------------
| Chaves de integração `fdx_` (1.8.0)
|--------------------------------------------------------------------------
*/

/**
 * @template T
 *
 * @param class-string<T> $class
 *
 * @return array{0: T, 1: QuantumTecnology\FalconDataHub\Tests\Support\FakeHttpClient}
 */
function makeResource(string $class, array $responses): array
{
    $config = makeConfig();
    $http   = fakeClient($responses);

    return [new $class($http, new TokenManager($http, $config, new TokenStore()), $config), $http];
}

it('cria a chave com nome, permissões, validade e aceite', function (): void {
    [$tokens, $http] = makeResource(IntegrationTokenResource::class, [
        jsonResponse(['data' => ['id' => 'uuid-1', 'key' => 'fdx_abcd1234_segredo']], 201),
    ]);

    $response = $tokens->create('Meu ERP', ['data:read', 'usage:read'], 90, riskAccepted: true);

    expect($http->urlAt(0))->toBe('https://datahub.test/panel/v1/integration-tokens')
        ->and($http->lastRequest()['payload'])->toBe([
            'name'            => 'Meu ERP',
            'abilities'       => ['data:read', 'usage:read'],
            'expires_in_days' => 90,
            'risk_accepted'   => true,
        ])
        ->and($response->data['key'])->toBe('fdx_abcd1234_segredo');
});

it('sem validade, não manda o campo (a chave não expira)', function (): void {
    [$tokens, $http] = makeResource(IntegrationTokenResource::class, [
        jsonResponse(['data' => ['id' => 'uuid-1']], 201),
    ]);

    $tokens->create('Loja', ['data:read'], riskAccepted: true);

    expect($http->lastRequest()['payload'])->toBe([
        'name'          => 'Loja',
        'abilities'     => ['data:read'],
        'risk_accepted' => true,
    ]);
});

it('lista, lê o catálogo e revoga pelo uuid', function (): void {
    [$tokens, $http] = makeResource(IntegrationTokenResource::class, [
        jsonResponse(['data' => []]),
        jsonResponse(['data' => [['value' => 'data:read', 'label' => 'Consultar dados', 'default' => true]]]),
        jsonResponse(['message' => 'Chave revogada.']),
    ]);

    $tokens->list();
    $tokens->abilities();
    $tokens->revoke('0199-uuid');

    expect($http->urlAt(0))->toBe('https://datahub.test/panel/v1/integration-tokens')
        ->and($http->urlAt(1))->toBe('https://datahub.test/panel/v1/integration-tokens/abilities')
        ->and($http->urlAt(2))->toBe('https://datahub.test/panel/v1/integration-tokens/0199-uuid')
        ->and($http->lastRequest()['method'])->toBe('DELETE');
});

it('403 vira ForbiddenException com a permissão que faltou', function (): void {
    // Até a 1.7, o 403 voltava como resposta comum: uma chave sem `ai:chat`
    // "funcionava" e devolvia um corpo de erro que ninguém conferia.
    [$cep] = makeResource(CepResource::class, [
        jsonResponse(['message' => 'A chave não tem a permissão ai:chat.', 'code' => 'MISSING_ABILITY', 'ability' => 'ai:chat'], 403),
    ]);

    try {
        $cep->search('01001000');
        $this->fail('Deveria ter lançado ForbiddenException.');
    } catch (ForbiddenException $e) {
        expect($e->getCode())->toBe(403)
            ->and($e->getAbility())->toBe('ai:chat')
            ->and($e->getMessage())->toContain('ai:chat');
    }
});

it('criar a chave antiga falha com mensagem clara, sem chamar a API', function (): void {
    [$legacy, $http] = makeResource(ApiKeyResource::class, []);

    expect(fn () => $legacy->create(['expires_in' => 30]))
        ->toThrow(FalconException::class, 'integrationTokens()->create()');

    expect($http->callCount())->toBe(0);
});
