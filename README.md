# Yampi-PHP-SDK

SDK oficial da plataforma de e-commerce [Yampi](https://yampi.com.br).

## Principais Recursos

* [x] Recurso de Login com JWT e Tokens de usuário.
* [x] Recurso de Requests.

## Dependências

* PHP >= 7.1

## Instalação via Composer

```bash
$ composer require somosyampi/yampi-php-sdk
```

## Utilizando a SDK

Você pode autenticar sua aplicação utilizando JWT (JSON Web Tokens).

```php
require 'vendor/autoload.php';

use Yampi\Api\AuthRequest;
use Yampi\Api\Exceptions\RequestException;
use Yampi\Api\Exceptions\ValidationException;

// Configure seu ambiente.
$yampiApi = AuthRequest::production();

// Configure sua loja.
$yampiApi->setMerchant('aliasDeSuaLoja');

try {
    // Faz o login por credenciais.
    $auth = $yampiApi->login(['email' => 'email@sualoja.com.br', 'password' => 'senha']);

    $type = $auth->getAuthTokenType(); // bearer

    $JWT = $auth->getAuthToken(); //eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
} catch (ValidationException $e) {
    // Erro de validação dos inputs

    // Mensagens de erros
    $errors = $e->getErrors();

} catch (RequestException $e) {
    // Credenciais inválidas.
}
```

Caso você você já possua um JWT.

```php
use Yampi\Api\AuthRequest;

// Seu JWT.
$JWT = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...';

// Configure seu JWT, seu ambiente e faz o login por JWT.
$yampiApi = AuthRequest::production()->setJwt($JWT);
```

Outra maneira de autenticação é pelo `token de usuário`:

```php
require 'vendor/autoload.php';

use Yampi\Api\AuthRequest;
use Yampi\Api\Exceptions\RequestException;

// Configure seu ambiente.
$yampiApi = AuthRequest::production();

// Configure sua loja.
$yampiApi->setMerchant('aliasDeSuaLoja');

// Configure seu token de usuário.
$yampiApi->setUserToken('seuTokenDeUsuário');

try {
    // Requests...
} catch (RequestException $e) {
    // Token de usuário inválido.
}
```

Uma vez autenticado, você já pode consumir a API da Yampi utilizando este SDK.

```php
use Yampi\Api\AuthRequest;

// Busque seu JWT.
$jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...';

// Configure seu JWT, seu ambiente e sua loja.
$yampiApi = AuthRequest::production()
    ->setJwt($jwt);
    ->setMerchant('aliasDeSuaLoja');

// Busca o catalogo de produtos da sua loja na Yampi.
$response = $yampiApi->request('GET', '/catalog/products');
// Ou
$response = $yampiApi->catalog()->products()->get();

$response->getData(); // array
```

Métodos que facilitam os recursos de pesquisa e filtros.

```php
// Busque seu JWT.
$jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...';

// Configure seu JWT, seu ambiente e sua loja.
$yampiApi = AuthRequest::production()
    ->setJwt($jwt)
    ->setMerchant('aliasDeSuaLoja');

// Filtra por página.
$yampiApi->page(2);

// Filtra por qualquer campo e altera o formato de busca dos campos sendo filtrados (LIKE).
$yampiApi->search(['name' => 'Roupa de Cama']);
$yampiApi->searchFields(['name' => 'like']);

// Filtra por data de criação (created_at)...
$yampiApi->period('2018-01-01', '2018-01-31');
// ...ou por qualquer campo de data.
$yampiApi->period('2018-01-01', '2018-01-31', 'any_date_field');

// Ordena por qualquer campos e altera a direção de orderação dos campos sendo ordenados.
$yampiApi->orderBy('name');
$yampiApi->sortedBy('desc');

// Altera o limite da paginação (máximo é 100).
$yampiApi->limit(20);

// Ignora o cache.
$yampiApi->skipCache();

// Retorna os produtos do catálogo com os filtros aplicados
$response = $yampiApi->request('GET', '/catalog/products');
// Ou
$response = $yampiApi->catalog()->products()->get();

$response->getData(); // array
```

Se preferir, pode encadear todas as chamadas diretamente:

```php
$catalogProducts = AuthRequest::production()
    ->setJwt($jwt)
    ->setMerchant('aliasDeSuaLoja')
    ->page(2)
    ->search(['name' => 'Roupa de Cama'])
    ->searchFields(['name' => 'like'])
    ->period('2018-01-01', '2018-01-31', 'created_at')
    ->orderBy('name')
    ->sortBy('desc')
    ->limit(20)
    ->skipCache()
    ->get();
```

### Paginação

```php
// ...
$response = $response->getData();
$pagination = $response->pagination();

$pagination->getTotal(); // Retorna o total de registros
$pagination->getPerPage(); // Retorna o total de registros por página
$pagination->getCurrentPage(); // Retorna a página atual
$pagination->getTotalPages(); // Retorna a quantidade total de páginas
$pagination->getNextLink(); // Retorna a URL da próxima página
$pagination->getPreviousLink(); // Retorna a URL da página anterior
```

## Change Log

Consulte [CHANGELOG](.github/CHANGELOG.md) para obter mais informações sobre o que mudou recentemente.

## Contribuições

Consulte [CONTRIBUTING](.github/CONTRIBUTING.md) para obter mais detalhes.

## Segurança

Se você descobrir quaisquer problemas relacionados à segurança, envie um e-mail para security@yampi.com.br em vez de usar as issues.
