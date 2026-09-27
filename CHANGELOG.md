# Changelog

Todas as mudanças relevantes do SDK PHP do Falcon Data Hub são documentadas aqui.

O formato segue [Keep a Changelog](https://keepachangelog.com/pt-BR/1.1.0/) e o versionamento segue [SemVer](https://semver.org/lang/pt-BR/).

**Este arquivo importa mais aqui do que num serviço.** Isto é um pacote: quem consome vê apenas um número de versão no `composer.json`, e hoje três serviços de produção dependem dele — `crmhub` (`^1.0`), `fiscalhub` (`^1.0`) e `trade` (`^1.5`). As duas primeiras constraints aceitam **qualquer 1.x**, então uma mudança de comportamento chega nesses serviços num `composer update` de rotina. Sem este registro, ninguém saberia o que mudou.

⚠️ **Publicar tem DOIS passos manuais.** Criar a tag no GitHub não publica: o **Packagist não está com auto-update**, então é preciso atualizar o pacote lá à mão depois de taguear. Enquanto isso não for feito, `composer require`/`update` continua entregando a versão anterior — e o sintoma é a correção "não ter funcionado" em quem atualizou de boa-fé.

⚠️ **Atualizar o pacote não basta.** Quem consome trava a versão no `composer.lock`: uma correção publicada aqui só chega ao serviço quando alguém roda o update **naquele** repositório. Vale para correções — e para regressões.

> **Nota sobre o histórico.** As versões `1.0.0` (2026-03-25) a `1.6.1` (2026-09-08) foram publicadas **sem changelog**; as entradas abaixo foram reconstruídas a partir das tags e das mensagens de commit, então descrevem *o que* mudou, não o *porquê* — esse contexto não foi registrado na época e o Git não o reconstrói. A partir da `1.7.0` o formato completo passa a valer.

## [Não lançado]

## [1.7.1] - 2026-09-27

### Adicionado

- **Suíte de testes — a primeira do pacote.** Até aqui o SDK tinha **156 métodos públicos em 46 arquivos**, distribuídos por composer para três serviços de produção, e **nenhum teste, nenhum `require-dev`, nenhum CI**. As três redes de proteção faltavam ao mesmo tempo.

  Os testes não saem para a rede, e não é por `Http::fake()` — o SDK fala cURL direto, sem Laravel. O que permite testar é a `HttpClientInterface`, que já existia: o `FalconClient` aceita um cliente injetado, e a suíte passa um `FakeHttpClient` que devolve respostas combinadas e grava o que foi pedido. Uma requisição sem resposta combinada **estoura**, em vez de devolver corpo vazio que o teste interpretaria como resposta da API — o equivalente ao `preventStrayRequests()` do Laravel.

  A cobertura começa pelo `ApiResponse`, de propósito: é por onde passa 100% do que a API devolve, e onde os erros do SDK são silenciosos. Quando o desempacotamento falha, o resultado não é exceção — é `null`, array vazio ou dado truncado, e o consumidor segue rodando com dado errado.

- ⚠️ **`php` corrigido de `^8.0` para `^8.1`.** O `composer.json` prometia 8.0, mas `src/Response/ApiResponse.php` usa `readonly`, que é **8.1+** — quem instalasse no 8.0 tomaria erro fatal de sintaxe, não uma recusa do composer. A constraint agora diz a verdade. Foi o CI recém-criado que expôs isso: sem matriz de versões, a promessa errada não tinha como aparecer.

- **CI** (`.github/workflows/tests.yml`) rodando em push e PR, em PHP 8.2 e 8.4, com `prefer-lowest` e `prefer-stable`. ⚠️ A matriz **não cobre o piso do pacote (8.1)**: o Pest arrasta o `brianium/paratest`, que exige PHP 8.2+, então a versão mínima suportada não é testável com este runner. Está escrito no workflow — melhor assumir a lacuna do que fingir uma cobertura que não existe.

### Corrigido

- **CNPJ alfanumérico apagado antes da consulta** (IN RFB 2.229/2024 · NT 2026.004). `cnpj()->search()`, `validate()->cnpj()` e `xml()->search()` limpavam o CNPJ com `sanitizeDigits` (`\D`), que apaga letras: `12.ABC.345/01DE-35` virava `123450135` e a API consultava outro documento, truncado. Novo `sanitizeDocument()` (maiúsculas, `[A-Z0-9]`) — o mesmo tratamento que o `VehicleResource` já dava à placa. CEP, CPF, NCM e CNAE continuam com `sanitizeDigits`.

- 🐛 **A paginação nunca funcionou — em nenhuma das 9 versões publicadas.** `ApiResponse::fromHttpResponse()` montava o `meta` procurando `current_page` em dois lugares: a raiz do corpo e dentro de `data`. Mas a API do Falcon Data Hub responde a paginação num bloco **irmão** de `data`:

  ```json
  { "data": [...], "pagination": { "current_page": 1, "per_page": 15, "total": 27 } }
  ```

  `pagination` não era consultado em lugar nenhum, então `meta` ficava **vazio** em toda listagem: sem `current_page`, `last_page`, `per_page` nem `total`.

  O sintoma não era erro — era **silêncio**. O consumidor recebia a primeira página e nada indicava que havia mais. Em `/private/v1/cities`, que tem milhares de registros, isso significa ver 15 e concluir que a lista acabou; dado truncado sem aviso vira decisão errada lá na frente. Passou despercebido por nove versões justamente porque não quebra nada ruidosamente.

  Os dois caminhos antigos continuam valendo — serviço que responda no formato anterior não pode quebrar por causa desta adição.

  ⚠️ **Antes de atualizar**, confira se o seu código já contorna isso lendo `pagination` na mão: com a correção, `meta` passa a vir preenchido, e um workaround que some as duas fontes contaria em dobro.

## [1.6.1] — 2026-09-08

### Alterado

- Documentação do resource de IA: timeout de 120s e como escolher o `max_tokens`.

## [1.6.0] — 2026-09-08

### Adicionado

- Resource de IA — `chat`, `ask` e `models`.

## [1.5.0] — 2026-07-11

### Adicionado

- Resource de Market Data: candles OHLCV (cripto).

## [1.4.1] — 2026-06-17

### Corrigido

- Endpoints de conta passam a usar o base path `panel/v1`.

## [1.4.0] — 2026-06-17

### Adicionado

- `UsageResource`, exposto no client.

## [1.3.0] — 2026-06-17

### Adicionado

- Endpoints de códigos de serviço municipais.

## [1.2.0] — 2026-06-16

### Adicionado

- Endpoints de códigos de serviço no `FiscalResource`.
- Lista de Serviço Nacional na documentação fiscal.

## [1.1.0] — 2026-05-23

### Adicionado

- Resource de consulta de veículos (placa) e seus accessors.

## [1.0.0] — 2026-03-25

### Adicionado

- Primeira versão pública: cliente PHP da API do Falcon Data Hub, com autenticação, retry e os resources de consulta (CEP, CNPJ, NCM, FIPE, BCB, entre outros).
