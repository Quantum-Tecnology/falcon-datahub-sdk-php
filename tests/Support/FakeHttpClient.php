<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Tests\Support;

use QuantumTecnology\FalconDataHub\Http\HttpClientInterface;
use QuantumTecnology\FalconDataHub\Http\HttpResponse;
use RuntimeException;

/**
 * Cliente HTTP de teste: devolve respostas combinadas e registra o que foi
 * pedido, sem tocar na rede.
 *
 * O SDK fala cURL direto, então não existe `Http::fake()` aqui. O que permite
 * testar é a `HttpClientInterface`: o `FalconClient` aceita um cliente
 * injetado, e esta implementação entra no lugar do `CurlHttpClient`.
 *
 * Uma requisição sem resposta combinada estoura — em vez de devolver um corpo
 * vazio que o teste interpretaria como "a API respondeu isso". Falhar alto é
 * o ponto: o equivalente ao `preventStrayRequests()` do Laravel.
 */
final class FakeHttpClient implements HttpClientInterface
{
    /** @var list<HttpResponse> */
    private array $queue;

    /** @var list<array{method: string, url: string, payload: array<string, mixed>, headers: array<string, string>}> */
    public array $recorded = [];

    /**
     * @param list<HttpResponse> $responses
     */
    public function __construct(array $responses = [])
    {
        $this->queue = $responses;
    }

    public function get(string $url, array $query = [], array $headers = []): HttpResponse
    {
        return $this->record('GET', $url, $query, $headers);
    }

    public function post(string $url, array $data = [], array $headers = []): HttpResponse
    {
        return $this->record('POST', $url, $data, $headers);
    }

    public function put(string $url, array $data = [], array $headers = []): HttpResponse
    {
        return $this->record('PUT', $url, $data, $headers);
    }

    public function delete(string $url, array $data = [], array $headers = []): HttpResponse
    {
        return $this->record('DELETE', $url, $data, $headers);
    }

    /** Quantas requisições o SDK disparou. */
    public function callCount(): int
    {
        return count($this->recorded);
    }

    /** A última requisição registrada, ou null se nenhuma. */
    public function lastRequest(): ?array
    {
        return $this->recorded[array_key_last($this->recorded)] ?? null;
    }

    /** A URL da requisição na posição informada (0 = primeira). */
    public function urlAt(int $index): ?string
    {
        return $this->recorded[$index]['url'] ?? null;
    }

    /**
     * @param array<string, mixed>  $payload
     * @param array<string, string> $headers
     */
    private function record(string $method, string $url, array $payload, array $headers): HttpResponse
    {
        $this->recorded[] = compact('method', 'url', 'payload', 'headers');

        if ([] === $this->queue) {
            throw new RuntimeException(sprintf(
                'FakeHttpClient: requisição %s %s sem resposta combinada. '
                . 'Acrescente uma resposta à fila — ou o teste está pedindo mais do que deveria.',
                $method,
                $url,
            ));
        }

        return array_shift($this->queue);
    }
}
