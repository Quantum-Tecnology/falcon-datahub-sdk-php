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

### Com token direto

```php
use QuantumTecnology\FalconDataHub\Falcon;
use QuantumTecnology\FalconDataHub\FalconConfig;

Falcon::configure(new FalconConfig(
    baseUrl: 'https://datahub.falcon-server.com.br',
    token: 'seu-token-bearer',
));
```

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
$prices  = Falcon::products()->prices(1, region: 'SP');
$results = Falcon::products()->search('arroz');
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

### API Keys

```php
$keys   = Falcon::apiKeys()->list();
$create = Falcon::apiKeys()->create(['expires_in' => 30]);
$delete = Falcon::apiKeys()->destroy(1);
```

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
