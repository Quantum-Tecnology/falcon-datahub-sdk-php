# Falcon DataHub SDK for PHP

SDK PHP para a API do Falcon DataHub. Consulte CEPs, CNPJs, NCMs, tabela FIPE, taxas BCB, e muito mais com uma interface fluida e zero dependencias de framework.

## Requisitos

- PHP >= 8.0
- ext-curl
- ext-json

## Instalacao

```bash
composer require quantumtecnology/falcon-datahub-sdk
```

## Configuracao

### Com chave de integração (recomendado)

```php
use QuantumTecnology\FalconDataHub\Falcon;
use QuantumTecnology\FalconDataHub\FalconConfig;

Falcon::configure(new FalconConfig(
    baseUrl: 'https://datahub.falcon-server.com.br',
    token: 'fdx_xxxxxxxx_...', // criada no painel, em Chaves de API
));
```

A chave `fdx_` é o jeito certo de um **sistema** falar com o DataHub: uma por sistema, com as permissões que ele usa (`data:read`, `ai:chat`, `xmls:write`, `usage:read`). Ela vale nas consultas (`/private`) e age como o dono da conta — o consumo conta na cota do plano dele.

⚠️ Ela **não** vale no painel (`/panel`) nem no admin: para gerenciar chaves, assinatura ou cartões, use o login por credenciais abaixo.

⚠️ **Evite o login por e-mail/senha num sistema que roda sozinho.** O DataHub revoga as sessões da conta a cada login: dois sistemas com a mesma conta derrubam um ao outro.

### Com login automatico

```php
Falcon::configure(FalconConfig::withCredentials(
    baseUrl: 'https://datahub.falcon-server.com.br',
    email: 'usuario@exemplo.com',
    password: 'sua-senha',
));
```

O SDK faz login automaticamente e cacheia o token em memoria. Se o token expirar (401), ele re-autentica e retenta a requisicao.

### Configuracoes opcionais

```php
Falcon::configure(new FalconConfig(
    baseUrl: 'https://datahub.falcon-server.com.br',
    token: 'seu-token',
    timeout: 30,          // Timeout da requisicao em segundos
    connectTimeout: 10,   // Timeout de conexao em segundos
    retries: 3,           // Tentativas em caso de falha (5xx/timeout)
    retryDelay: 2000,     // Delay entre tentativas em ms
    cache: $psrCache,     // PSR-16 CacheInterface (persistir token)
    logger: $psrLogger,   // PSR-3 LoggerInterface (debug)
));
```

### Uso com injecao de dependencia

```php
use QuantumTecnology\FalconDataHub\FalconClient;

$client = new FalconClient(new FalconConfig(
    baseUrl: 'https://datahub.falcon-server.com.br',
    token: 'seu-token',
));

$result = $client->cep()->search('13472360');
```

## Uso

### IA (Inteligencia Artificial)

Atendida pela GPU da Falcon, com transbordo automatico para provedor pago
quando ela nao da conta. Cobrada por **token** (entrada e saida contam
separado), com cota mensal por plano.

```php
// Pergunta de uma linha
$r = Falcon::ai()->ask('Explique o que e um CNPJ em uma frase.');
echo $r->data['choices'][0]['message']['content'];

// Conversa completa (formato OpenAI)
$r = Falcon::ai()->chat([
    ['role' => 'system', 'content' => 'Responda em portugues, seja breve.'],
    ['role' => 'user',   'content' => 'O que e NCM?'],
], 'falcon-fast');

// Quanto custou
$r->data['usage']['prompt_tokens'];      // entrada
$r->data['usage']['completion_tokens'];  // saida

// Modelos aceitos
Falcon::ai()->models();
```

**Modelo pelo nome logico**, nunca pelo fisico: `falcon-fast` responde em
segundos, `falcon-quality` escreve melhor. O modelo real por tras pode mudar
sem aviso — o nome logico continua valendo.

#### ⚠️ Timeout: use 120s, nao 30s

A GPU gera de 35 a 75 tokens por segundo. Uma resposta de ~800 tokens leva
**20 a 25 segundos**, e o prompt tambem conta (3.000 tokens de historico somam
alguns segundos antes de a resposta comecar).

```php
Falcon::configure(new FalconConfig(
    baseUrl: 'https://beta.falcon-server.com.br/data-hub',
    email: '...', password: '...',
    timeout: 120,   // <- o padrao de 30s NAO basta para IA
));
```

Com 30s, respostas longas estouram — e o sintoma engana: nao vem erro nenhum,
a chamada so nao retorna. Foi assim que uma IA de atendimento "parou de
responder" justamente as perguntas que pediam o catalogo, que geram texto
longo.

#### `max_tokens`: o teto e do SEU canal

O padrao do servidor e **2000** (teto 4096), generoso porque este servico
atende varios produtos — resumo fiscal e analise precisam de espaco. Mas o
limite certo depende de onde a resposta vai aparecer:

| destino | sugestao | por que |
|---|---|---|
| WhatsApp / chat | 600-800 | ninguem le oito paragrafos no celular; corta o tempo pela metade |
| E-mail / resumo | 1500-2000 | texto estruturado precisa de espaco |
| Analise / relatorio | 2000-4096 | o teto existe para isso |

```php
Falcon::ai()->chat($messages, 'falcon-quality', ['max_tokens' => 800]);
```

⚠️ **Baixo demais devolve resposta VAZIA.** Modelo de reasoning gasta a cota
pensando antes de escrever: abaixo de ~500 e arriscado, e com 400 ja foi medido
voltar em branco. Se a resposta vier vazia, o primeiro palpite e este.

> O endpoint segue o contrato da **OpenAI** (`/chat/completions`). Se voce ja
> usa um SDK de IA, da para apontar a `base_url` dele para o DataHub em vez de
> passar por aqui; este resource existe para nao precisar de um segundo cliente
> HTTP no projeto.

O consumo do mes aparece em `Falcon::usage()->show()`, no bloco `ai`.

### CEP (Codigo Postal)

```php
$result = Falcon::cep()->search('13472360');
// $result->success    // bool
// $result->data       // array com dados do CEP
// $result->message    // string
```

### CNPJ (Cadastro de Pessoa Juridica)

```php
$result = Falcon::cnpj()->search('11.222.333/0001-44');
// Aceita com ou sem mascara — caracteres nao-numericos sao removidos automaticamente
```

### CNAE (Atividade Economica)

```php
$result = Falcon::cnae()->search('6201-5');
```

### NCM (Nomenclatura Comum do Mercosul)

```php
$result = Falcon::ncm()->search('84713012');
```

### IP (Geolocalizacao)

```php
$result = Falcon::ip()->search('8.8.8.8');
$result = Falcon::ip()->search('8.8.8.8', databaseId: 1);
```

### Acoes / Simbolos (Bolsa)

```php
$result = Falcon::action()->search('PETR4');
```

### Market Data / Candles (Cripto)

Candles OHLCV de cripto, coletados da Binance sob demanda e cacheados no DataHub.
O par aceita `BTC/USDT` ou `BTCUSDT`; o timeframe segue os intervalos da Binance
(`1m`, `5m`, `15m`, `30m`, `1h`, `4h`, `1d`, `1w`).

```php
// Candles estruturados (para exibir/consumir)
$candles = Falcon::marketData()->candles('BTC/USDT', '1h', '2026-07-01', '2026-07-10');

// Dump cru [[ts_ms, open, high, low, close, volume], ...] — formato Binance/Freqtrade.
// Pronto para gravar data/binance/{PAR}_{QUOTE}-{tf}.json e rodar o backtest.
$dump = Falcon::marketData()->dump('BTC/USDT', '5m', '2026-07-01', '2026-07-10');

// Pares ja cacheados + cobertura
$pairs = Falcon::marketData()->pairs();
```

### Cidades e Estados

```php
$states = Falcon::states()->list();
$state  = Falcon::states()->show(35); // Sao Paulo

$cities = Falcon::cities()->list(stateId: 35);
$city   = Falcon::cities()->show(3550308); // Sao Paulo capital
```

### Validacao (CPF, CNPJ, PIX)

```php
$result = Falcon::validate()->cpf('123.456.789-09');
$result = Falcon::validate()->cnpj('11222333000144');
$result = Falcon::validate()->pix('email@exemplo.com');

// Gerar documentos validos (para testes)
$cpf  = Falcon::validate()->generateCpf();
$cnpj = Falcon::validate()->generateCnpj();
```

### Brasil (DDD, Bancos, Feriados)

```php
$result = Falcon::brasil()->ddd('11');        // DDD de Sao Paulo
$banks  = Falcon::brasil()->banks();          // Todos os bancos
$bank   = Falcon::brasil()->bank('001');      // Banco do Brasil
$fds    = Falcon::brasil()->holidays(2026);   // Feriados de 2026
```

### BCB (Banco Central — Indicadores)

```php
$selic    = Falcon::bcb()->selic();
$cdi      = Falcon::bcb()->cdi();
$ipca     = Falcon::bcb()->ipca(2026);
$cambio   = Falcon::bcb()->currency('USD', 'BRL');
```

### FIPE (Tabela de Veiculos)

```php
$brands = Falcon::fipe()->brands('carros');
$models = Falcon::fipe()->models('carros', '59');
$years  = Falcon::fipe()->years('carros', '59', '5940');
$price  = Falcon::fipe()->price('carros', '59', '5940', '2024-1');
```

### Fiscal (CFOP, CST, Lista de Serviço Nacional)

```php
$cfops = Falcon::fiscal()->cfopList();
$cfop  = Falcon::fiscal()->cfop('5102');
$csts  = Falcon::fiscal()->cstList('ICMS');
$cst   = Falcon::fiscal()->cst('ICMS', '00');

// Lista de Serviço Nacional (cTribNac) — NFS-e Padrão Nacional
$servicos = Falcon::fiscal()->serviceCodesList('software');       // busca paginada
$servicos = Falcon::fiscal()->serviceCodesList(item: 1, page: 2); // filtra item da LC 116
$servico  = Falcon::fiscal()->serviceCode(42);                    // por id
```

### Produtos (Inteligencia de Precos)

```php
$product = Falcon::products()->findByEan('7891234567890', region: 'SP');
$results = Falcon::products()->search('arroz');

// Id do produto como a API devolve (hashid, vindo da busca ou do EAN)
$prices = Falcon::products()->prices('v5rNVenmKP', region: 'SP');

// Periodo de observacao: a partir de, ate, ou entre (data pura = dia inteiro)
$marco = Falcon::products()->prices('v5rNVenmKP', observedFrom: '2026-03-01', observedTo: '2026-03-31');
$ate   = Falcon::products()->prices('v5rNVenmKP', observedTo: '2026-03-21');

// So produtos com preco coletado na(s) loja(s) — ids de /public/v1/stores
$naLoja = Falcon::products()->search('arroz', storeIds: 'Ke7wJd3NxF');
$emDuas = Falcon::products()->search('arroz', storeIds: ['Ke7wJd3NxF', 'Yr2gPb5LhC']);
```

### Delivery (Rotas e Distancias)

```php
$route    = Falcon::delivery()->bestRoute([...]);
$distance = Falcon::delivery()->calculateDistance([...]);
```

### XML (NFe)

```php
$xmls   = Falcon::xml()->list();
$search = Falcon::xml()->search('11222333000144');
$xml    = Falcon::xml()->show(1);
$upload = Falcon::xml()->upload(['xml' => '...']);
$del    = Falcon::xml()->destroy(1);
```

### Cartoes de Credito

```php
$cards  = Falcon::creditCards()->list();
$create = Falcon::creditCards()->create([...]);
$delete = Falcon::creditCards()->destroy(1);
```

### Planos e Assinaturas

```php
$plans  = Falcon::plans()->list();
$plan   = Falcon::plans()->show(1);

$subs   = Falcon::subscriptions()->list();
$active = Falcon::subscriptions()->active();
$create = Falcon::subscriptions()->create(['plan_id' => 1, 'credit_card_id' => 1]);
$change = Falcon::subscriptions()->changePlan(['plan_id' => 2, 'credit_card_id' => 1]);
$cancel = Falcon::subscriptions()->cancel();
```

### Chaves de integração (`fdx_`)

Exige sessão de usuário (login por credenciais): o painel recusa uma chave `fdx_`.

```php
$catalogo = Falcon::integrationTokens()->abilities();   // as permissões disponíveis
$chaves   = Falcon::integrationTokens()->list();

$nova = Falcon::integrationTokens()->create(
    name: 'Meu ERP',
    abilities: ['data:read', 'usage:read'],
    expiresInDays: 90,          // null = não expira
    riskAccepted: true,         // obrigatório: a chave consome a cota da conta
);
$chave = $nova->data['key'];    // ⚠️ aparece SÓ aqui — guarde na hora

Falcon::integrationTokens()->revoke($nova->data['id']);
```

Chave sem a permissão da rota recebe `ForbiddenException`, e `getAbility()` diz qual faltou.

### Chave antiga (`api-key`) — em transição

```php
$antigas = Falcon::apiKeys()->list();       // inclui `sunset_at`, a data de corte
Falcon::apiKeys()->destroy(1);              // revogar depois de trocar pela fdx_
```

`apiKeys()->create()` lança exceção desde a 1.8.0: o DataHub não cria mais este tipo de chave.

### Sessoes de Acesso

```php
$recent  = Falcon::accessSessions()->recent();
$blocked = Falcon::accessSessions()->blocked();
$block   = Falcon::accessSessions()->block(['ip' => '1.2.3.4', 'reason' => 'Suspeito']);
$unblock = Falcon::accessSessions()->unblock(1);
```

### Autenticacao

```php
$login    = Falcon::auth()->login('email@ex.com', 'senha');
$register = Falcon::auth()->register([
    'name'     => 'Nome',
    'email'    => 'email@ex.com',
    'password' => 'senha',
    'password_confirmation' => 'senha',
]);
$forgot   = Falcon::auth()->forgotPassword('email@ex.com');
```

## Tratamento de Erros

O SDK lanca exceptions tipadas para cada cenario:

```php
use QuantumTecnology\FalconDataHub\Exceptions\AuthException;
use QuantumTecnology\FalconDataHub\Exceptions\ForbiddenException;
use QuantumTecnology\FalconDataHub\Exceptions\NotFoundException;
use QuantumTecnology\FalconDataHub\Exceptions\RateLimitException;
use QuantumTecnology\FalconDataHub\Exceptions\ServerException;
use QuantumTecnology\FalconDataHub\Exceptions\TimeoutException;
use QuantumTecnology\FalconDataHub\Exceptions\ValidationException;

try {
    $result = Falcon::cnpj()->search('00000000000000');
} catch (NotFoundException $e) {
    // CNPJ nao encontrado
    echo $e->getMessage();
} catch (ValidationException $e) {
    // Dados invalidos
    print_r($e->getErrors());
} catch (RateLimitException $e) {
    // Limite de requisicoes excedido
    echo "Tente novamente em {$e->getRetryAfter()} segundos";
} catch (ForbiddenException $e) {
    // A chave fdx_ nao tem a permissao da rota
    echo "Falta a permissao {$e->getAbility()}";
} catch (AuthException $e) {
    // Token invalido ou expirado
} catch (ServerException $e) {
    // Erro no servidor (5xx)
} catch (TimeoutException $e) {
    // Timeout na requisicao
}
```

Todas as exceptions extendem `FalconException` e carregam o `ApiResponse` original:

```php
try {
    $result = Falcon::cep()->search('00000000');
} catch (FalconException $e) {
    $response = $e->getResponse(); // ApiResponse|null
    echo $e->getCode();            // HTTP status code
}
```

## Cache de Token com PSR-16

Para persistir o token entre requisicoes (ex: Redis, arquivo):

```php
// Qualquer implementacao PSR-16 (Symfony Cache, Laravel Cache, etc.)
$cache = new \Symfony\Component\Cache\Psr16Cache(
    new \Symfony\Component\Cache\Adapter\RedisAdapter($redis),
);

Falcon::configure(new FalconConfig(
    baseUrl: 'https://datahub.falcon-server.com.br',
    email: 'usuario@exemplo.com',
    password: 'senha',
    cache: $cache,
));
```

## Resposta (ApiResponse)

Todos os metodos retornam um `ApiResponse` com propriedades `readonly`:

```php
$result = Falcon::cep()->search('13472360');

$result->success;    // bool
$result->statusCode; // int (200, 201, etc.)
$result->message;    // string
$result->data;       // array
$result->errors;     // array (vazio em sucesso)
$result->meta;       // array (paginacao quando aplicavel)

$result->toArray();  // array completo
```

## Licenca

MIT
