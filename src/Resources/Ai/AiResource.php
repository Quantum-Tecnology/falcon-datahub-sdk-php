<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Ai;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

/**
 * IA do DataHub — atendida pela GPU da Falcon, com transbordo automático para
 * provedor pago quando ela não dá conta.
 *
 * O endpoint segue o contrato da **OpenAI** (`/chat/completions`), então quem
 * já usa um SDK de IA pode apontar para a URL do DataHub em vez de passar por
 * aqui. Este resource existe para quem já usa o SDK e não quer um segundo
 * cliente HTTP no projeto.
 *
 * Cobrado por TOKEN (entrada e saída contam separado), com cota mensal por
 * plano. O consumo aparece em `usage()->show()`, no bloco `ai`.
 */
final class AiResource extends AbstractResource
{
    /**
     * Manda uma conversa e recebe a próxima fala da IA.
     *
     * `$messages` segue o formato da OpenAI:
     *   [['role' => 'system'|'user'|'assistant', 'content' => '...'], ...]
     *
     * `$model` aceita os nomes LÓGICOS — `falcon-fast` (responde em segundos)
     * ou `falcon-quality` (escreve melhor, demora um pouco mais). Nunca peça o
     * nome físico do modelo: ele muda quando trocamos o motor, e o lógico não.
     *
     * ## Quanto isso demora, e o que fazer a respeito
     *
     * A GPU gera de 35 a 75 tokens por segundo (medido). Uma resposta de ~800
     * tokens leva **20 a 25 segundos** — e o prompt conta: 3.000 tokens de
     * histórico somam alguns segundos antes de a resposta começar.
     *
     * Por isso, **configure o timeout do seu cliente HTTP para pelo menos
     * 120s**. Com 30s (o padrão de muitas libs) respostas longas estouram, e o
     * sintoma engana: não vem erro nenhum, a chamada só não retorna. Foi assim
     * que a IA de atendimento "parou de responder" certas mensagens — as que
     * pediam o catálogo, justamente as que geram texto longo.
     *
     * ## `max_tokens`: o teto é do SEU canal
     *
     * O padrão do servidor (2000) é generoso porque este serviço atende vários
     * produtos — resumo fiscal e análise precisam de espaço. Mas o limite certo
     * depende de onde a resposta vai aparecer: para WhatsApp, 800 já é
     * bastante (ninguém lê oito parágrafos no celular) e corta o tempo pela
     * metade. Defina no seu lado; não conte com o padrão do serviço.
     *
     * ⚠️ Baixo DEMAIS devolve resposta **vazia**: modelo de reasoning gasta a
     * cota pensando antes de escrever. Abaixo de ~500 é arriscado, e com 400 já
     * foi medido voltar em branco.
     *
     * @param array<int, array{role: string, content: string}> $messages
     * @param array<string, mixed>                             $options `max_tokens` (padrão 2000, teto 4096), `temperature`
     */
    public function chat(array $messages, string $model = 'falcon-quality', array $options = []): ApiResponse
    {
        return $this->post('private/v1/ai/chat/completions', [
            'model'    => $model,
            'messages' => $messages,
            ...$options,
        ]);
    }

    /**
     * Atalho para a pergunta de uma linha só.
     *
     * `$system` é a instrução de comportamento ("responda em português, seja
     * breve"); `$prompt` é a pergunta em si.
     */
    public function ask(string $prompt, ?string $system = null, string $model = 'falcon-quality'): ApiResponse
    {
        $messages = [];

        if (null !== $system && '' !== $system) {
            $messages[] = ['role' => 'system', 'content' => $system];
        }

        $messages[] = ['role' => 'user', 'content' => $prompt];

        return $this->chat($messages, $model);
    }

    /** Modelos lógicos que a API aceita hoje. */
    public function models(): ApiResponse
    {
        return $this->get('private/v1/ai/models');
    }
}
