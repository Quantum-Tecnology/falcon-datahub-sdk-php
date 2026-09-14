<?php

declare(strict_types = 1);

use QuantumTecnology\FalconDataHub\Response\ApiResponse;

/*
|--------------------------------------------------------------------------
| ApiResponse — o desempacotador
|--------------------------------------------------------------------------
|
| Por aqui passa 100% do que a API devolve, e é onde os erros do SDK são
| silenciosos: quando o desempacotamento falha, o resultado não é exceção — é
| `null`, array vazio ou dado truncado. O consumidor segue rodando com dado
| errado, e o sintoma aparece longe da causa.
|
| Foi exatamente assim que o bug da paginação passou despercebido por 9
| versões publicadas.
|
*/

it('desempacota o envelope padrão da casa', function (): void {
    $response = ApiResponse::fromHttpResponse(jsonResponse([
        'success' => true,
        'message' => 'Consulta realizada com sucesso',
        'data'    => ['id' => '01001000', 'state' => 'SP'],
    ]));

    expect($response->success)->toBeTrue()
        ->and($response->statusCode)->toBe(200)
        ->and($response->message)->toBe('Consulta realizada com sucesso')
        ->and($response->data['state'])->toBe('SP');
});

it('trata corpo SEM envelope como o próprio data', function (): void {
    // Nem toda rota devolve {success, message, data}. Sem este fallback, quem
    // consome uma rota "crua" receberia data vazio.
    $response = ApiResponse::fromHttpResponse(jsonResponse([
        'id'    => 1,
        'nome'  => 'São Paulo',
    ]));

    expect($response->data['nome'])->toBe('São Paulo')
        ->and($response->success)->toBeTrue();
});

it('marca insucesso quando o status é de erro', function (): void {
    $response = ApiResponse::fromHttpResponse(jsonResponse([
        'message' => 'Registro não encontrado',
    ], 404));

    expect($response->success)->toBeFalse()
        ->and($response->statusCode)->toBe(404)
        ->and($response->message)->toBe('Registro não encontrado');
});

it('preserva os errors de validação', function (): void {
    // 422 sem os errors deixa o consumidor sem saber QUAL campo recusou.
    $response = ApiResponse::fromHttpResponse(jsonResponse([
        'message' => 'Dados inválidos',
        'errors'  => ['plan_id' => ['O campo plan_id é obrigatório.']],
    ], 422));

    expect($response->success)->toBeFalse()
        ->and($response->errors)->toHaveKey('plan_id');
});

it('envelopa data escalar num array', function (): void {
    // `data` string/int quebraria o tipo declarado da propriedade.
    $response = ApiResponse::fromHttpResponse(jsonResponse([
        'success' => true,
        'message' => 'OK',
        'data'    => 'texto-simples',
    ]));

    expect($response->data)->toBe(['texto-simples']);
});

it('lê a paginação quando ela vem na RAIZ do corpo', function (): void {
    $response = ApiResponse::fromHttpResponse(jsonResponse([
        'data'         => [['id' => 1]],
        'current_page' => 2,
        'last_page'    => 5,
        'per_page'     => 15,
        'total'        => 68,
    ]));

    expect($response->meta['current_page'])->toBe(2)
        ->and($response->meta['total'])->toBe(68);
});

it('lê a paginação quando ela vem DENTRO de data', function (): void {
    // Formato do paginador padrão do Laravel.
    $response = ApiResponse::fromHttpResponse(jsonResponse([
        'data' => [
            'current_page' => 1,
            'per_page'     => 15,
            'total'        => 27,
            'data'         => [['id' => 1], ['id' => 2]],
        ],
    ]));

    expect($response->meta['total'])->toBe(27)
        // A lista foi desaninhada: o consumidor recebe os itens, não o envelope.
        ->and($response->data)->toHaveCount(2);
});

it('lê a paginação do bloco `pagination` — o formato REAL do DataHub', function (): void {
    // ⭐ O caso que faltava, e que nenhuma versão publicada atendia.
    //
    // A API do DataHub responde a paginação num bloco IRMÃO de `data`:
    //     { data: [...], pagination: { current_page, per_page, total } }
    //
    // O `fromHttpResponse` procurava `current_page` só na raiz e dentro de
    // `data`. Resultado: `meta` vazio, e o consumidor não tinha como saber que
    // havia mais páginas — o sintoma era "só vem a primeira página", SEM erro.
    // Em /private/v1/cities (milhares de registros) isso significa ver 15 e
    // concluir que acabou.
    $response = ApiResponse::fromHttpResponse(jsonResponse([
        'success'    => true,
        'message'    => 'OK',
        'data'       => [['id' => 1], ['id' => 2]],
        'pagination' => [
            'current_page' => 1,
            'last_page'    => 2,
            'per_page'     => 15,
            'total'        => 27,
        ],
    ]));

    expect($response->meta['current_page'])->toBe(1)
        ->and($response->meta['last_page'])->toBe(2)
        ->and($response->meta['per_page'])->toBe(15)
        ->and($response->meta['total'])->toBe(27)
        // `data` continua sendo a lista de itens.
        ->and($response->data)->toHaveCount(2);
});

it('não inventa meta quando não há paginação', function (): void {
    // Consulta pontual (um CEP) não é lista: meta vazio é o correto, e o
    // consumidor usa isso para distinguir os dois casos.
    $response = ApiResponse::fromHttpResponse(jsonResponse([
        'success' => true,
        'message' => 'OK',
        'data'    => ['id' => '01001000'],
    ]));

    expect($response->meta)->toBe([]);
});
