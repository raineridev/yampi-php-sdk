<?php

namespace Yampi\Api;

use GuzzleHttp\Client as Client;
use GuzzleHttp\Exception\ClientException;
use Teapot\StatusCode;
use Yampi\Api\Exceptions\InvalidIncludeException;
use Yampi\Api\Exceptions\RequestException;
use Yampi\Api\Exceptions\ValidationException;
use Yampi\Api\Exceptions\InvalidMethodException;
use Yampi\Api\Exceptions\InvalidParamException;
use Yampi\Api\Exceptions\InvalidSearchValueException;

class Request
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $method = 'GET';

    /**
     * @var string
     */
    protected $merchant;

    /**
     * @var string
     */
    protected $route;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $query = [];

    /**
     * @var array
     */
    protected $body = [];

    /**
     * @var string
     */
    protected $userAgent = 'yampi-php-sdk';

    /**
     * @var string
     */
    protected $version = 'v2';

    /**
     * @var bool
     */
    protected $forceAlias = false;

    /**
     * @var bool
     */
    protected $forgetAlias = false;

    /**
     * @var Client
     */
    protected $client;

    /**
     * Request constructor.
     *
     * @param string $url - API route
     * @param Client $client - Optional Dependency Injection for GuzzleHttp\Client
     */
    public function __construct(string $url, Client $client = null)
    {
        $this->url = rtrim($url, '/');
        $this->client = $client ?: new Client();
    }

    /**
     * Used to add URL parts with an easy to use API
     *
     * @param string $name Method called
     * @param array $arguments Parameters passed to the method
     * @return self
     */
    public function __call($name, $arguments) : self
    {
        $this->setRoute(
            implode('/', array_merge([
                $this->route,
                $name,
            ], $arguments))
        );

        return $this;
    }

    /**
     * Get Yampi API version
     *
     * @return string Version
     */
    public function getVersion() : string
    {
        return $this->version;
    }

    /**
     * Get Yampi API base route
     *
     * @return string URL
     */
    public function getUrl() : string
    {
        return $this->url;
    }

    /**
     * Force Merchant Alias to be used
     *
     * @return self
     */
    public function forceAlias() : self
    {
        $this->forceAlias = true;

        return $this;
    }

    /**
     * Disable Merchant Alias
     *
     * @return self
     */
    public function forgetAlias() : self
    {
        $this->forgetAlias = true;

        return $this;
    }

    /**
     * Get User-Agent being used for all requests
     *
     * @return string
     */
    public function getUserAgent() : string
    {
        return $this->userAgent;
    }

    /**
     * Sets the request's HTTP method.
     *
     * @param string $method
     * @throws InvalidMethodException if the method is invalid
     */
    public function setMethod($method) : self
    {
        if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
            throw new InvalidMethodException($method . ' is not a valid HTTP method. Use GET, POST, PUT, PATCH or DELETE instead.', $this);
        }

        $this->method = $method;

        return $this;
    }

    /**
     * Sets the request's API route.
     *
     * @param string $route
     * @throws RequestException if anything gets wrong.
     */
    public function setRoute($route) : self
    {
        $this->route = ltrim($route, '/');

        return $this;
    }

    /**
     * Resets route
     *
     * @return self
     */
    public function resetRoute() : self
    {
        $this->setRoute('');

        return $this;
    }

    /**
     * Sets a body param to the query or body property.
     *
     * @param string $property 'query' or 'body'
     * @param string $key The key to set
     * @param string $value The value
     * @throws Yampi\RequestException if anything gets wrong.
     */
    public function setParam($property, $key, $value) : self
    {
        if (!in_array($property, ['headers', 'query', 'body'])) {
            throw new InvalidParamException($property . ' is not a valid property for Request. Use headers, query or body.', $this);
        }

        $this->{$property}[$key] = $value;

        return $this;
    }

    /**
     * Sets the request body.
     *
     * @param array $body
     */
    public function setBody($body) : self
    {
        $this->body = [];

        $this->addBody($body);

        return $this;
    }

    /**
     * Adds some data to the request body.
     *
     * @param array $body
     */
    public function addBody($body) : self
    {
        foreach ($body as $key => $param) {
            $this->setParam('body', $key, $param);
        }

        return $this;
    }

    /**
     * Gets the request body.
     *
     * @param array $body
     */
    public function getBody() : array
    {
        return $this->body;
    }

    /**
     * Sets the request query.
     *
     * @param array $key
     * @param array $value
     */
    public function setQuery($key, $value) : self
    {
        return $this->addQueries([$key => $value]);
    }

    /**
     * Adds attributes to query param.
     *
     * @param array $queries
     */
    public function addQueries(array $queries) : self
    {
        foreach ($queries as $key => $param) {
            $this->setParam('query', $key, $param);
        }

        return $this;
    }

    /**
     * Gets the request query.
     *
     * @return array
     */
    public function getQuery() : array
    {
        return $this->query;
    }

    /**
     * Clears the query params.
     *
     * @return self
     */
    public function clearQuery() : self
    {
        $this->query = [];

        return $this;
    }

    /**
     * Sets the request's merchant alias.
     *
     * @param string $alias
     */
    public function setMerchant($alias) : self
    {
        $this->merchant = ltrim($alias, '/');

        return $this;
    }

    /**
     * Adds a include query string parameter. Alias to 'include'. Code example:
     * <code>
     * $yampi->includes(['sku']);
     * $yampi->includes('merchant');
     * </code>
     *
     * @param array|string $include Parameter to include
     * @param bool $append Append to existing include
     * @throws InvalidIncludeException When any included value is not valid
     * @return self
     */
    public function includes($include, $append = true) : self
    {
        return $this->include($include, $append);
    }

    /**
     * Adds a include query string parameter. Code example:
     * <code>
     * $yampi->search(['name' => 'Entity\'s Name']);
     * </code>
     *
     * @param array|string $include Parameter to include
     * @param bool $append Append to existing include
     * @throws InvalidIncludeException When any included value is not valid
     * @return self
     */
    public function include($include, $append = true) : self
    {
        if (!is_array($include)) {
            $include = [$include];
        }

        foreach ($include as $value) {
            if (!is_string($value) || empty($value)) {
                throw new InvalidIncludeException('Include must be a not empty string', $this);
            }
        }

        if ($append && array_key_exists('include', $this->query)) {
            array_unshift($include, $this->query['include']);
        }

        $this->setQuery('include', implode(',', $include));

        return $this;
    }

    /**
     * Adds search parameters. Code example:
     * <code>
     * $yampi->search(['name' => 'Entity\'s Name']);
     * </code>
     *
     * @param array|string $search Parameters to add
     * @param bool $append Append to existing include
     * @return self
     */
    public function search($search, $append = true) : self
    {
        return $this->genericSearch('search', $search, $append);
    }

    /**
     * Adds searchField parameters. Code example:
     * <code>
     * $yampi->searchFields(['name' => 'like']);
     * </code>
     *
     * @param array|string $searchFields Parameters to add
     * @param bool $append Append to existing include
     * @return self
     */
    public function searchFields($searchFields, $append = true) : self
    {
        return $this->genericSearch('searchFields', $searchFields, $append);
    }

    /**
     * Adds a date query param
     *
     * @param string $start The starting date, format YYYY-MM-DD
     * @param string $end The starting date, format YYYY-MM-DD
     * @return self
     */
    public function period(string $start, string $end, $field = 'created_at') : self
    {
        $this->setQuery('date', $field . ':' . $start . '|' . $end);

        return $this;
    }

    /**
     * Removes the date query param
     *
     * @return self
     */
    public function noPeriod() : self
    {
        unset($this->query['date']);

        return $this;
    }

    /**
      * Adds an orderBy query param
      *
      * @param string $orderBy The orderBy attribute
      * @return self
      */
    public function orderBy($orderBy) : self
    {
        $this->setQuery('orderBy', $orderBy);

        return $this;
    }

    /**
      * Removes orderBy query param
      *
      * @return self
      */
    public function noOrderBy() : self
    {
        unset($this->query['orderBy']);

        return $this;
    }

    /**
     * Set sortedBy query param. Alias of sortBy()
     *
     * @param string $sortedBy Field to sort by
     * @return self
     */
    public function sortedBy(string $sortedBy)
    {
        return $this->sortBy($sortedBy);
    }

    /**
     * Set sortedBy query param
     *
     * @param string $sortBy Field to sort by
     * @return self
     */
    public function sortBy(string $sortBy = null)
    {
        $this->setQuery('sortedBy', $sortBy);

        return $this;
    }

    /**
     * Removes sortBy query param
     *
     * @return self
     */
    public function noSortBy()
    {
        unset($this->query['sortedBy']);

        return $this;
    }

    /**
     * Set payload limit to query
     *
     * @param int $page Page to search
     * @return self
     */
    public function limit($limit)
    {
        $this->setQuery('limit', $limit);

        return $this;
    }

    /**
     * Removes any limit from query
     *
     * @return self
     */
    public function noLimit()
    {
        unset($this->query['limit']);

        return $this;
    }

    /**
     * Set page to query
     *
     * @param int $page Page to search
     * @return self
     */
    public function page(int $page)
    {
        $this->setQuery('page', $page);

        return $this;
    }

    /**
     * Removes any page from query
     *
     * @return self
     */
    public function noPage()
    {
        unset($this->query['page']);

        return $this;
    }

    /**
     * Set the skipCache query param
     *
     * @return self
     */
    public function skipCache()
    {
        $this->setQuery('skipCache', 'true');

        return $this;
    }

    /**
     * Gets the request's HTTP method.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Gets the request's API route.
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Gets the request full route.
     *
     * @return string
     */
    public function getFullRoute()
    {
        $merchant = '';
        $fragments = explode('/', $this->route);
        $ignorePath = in_array($fragments[0], ['auth', 'users', 'pvt']);

        if (!$this->forgetAlias && (!$ignorePath || $this->forceAlias)) {
            $merchant = $this->merchant;
        }

        return implode('/', array_filter([
            $this->url,
            $this->version,
            $merchant,
            $this->route,
        ]));
    }

    /**
     * Gets the request's body.
     *
     * @return array
     */
    public function getRequestBody()
    {
        $body = [
            'headers' => array_merge($this->headers, [
                'User-Agent' => $this->getUserAgent(),
            ]),
            'query' => $this->query,
        ];

        if ($this->getMethod() === 'GET') {
            $body['query'] = array_merge($this->query, $this->body);

            return $body;
        }

        $body['json'] = $this->body;

        return $body;
    }

    /**
     * Adds a new header parameter
     *
     * @return array
     */
    public function addHeader($key, $value)
    {
        $this->setParam('headers', $key, $value);

        return $this;
    }

    /**
     * Gets headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Requests Yampi API.
     *
     * @param string $method
     * @param string $route
     * @param array $body
     * @throws RequestException if anything gets wrong
     * @throws ValidationException if any parameter is wrong
     * @return Response
     */
    public function request($method = '', $route = '', $body = []) : Response
    {
        $this->setMethod($method ?: $this->method);
        $this->setRoute($route ?: $this->route);
        $this->addBody($body);

        try {
            $request = $this->client->request($this->getMethod(), $this->getFullRoute(), $this->getRequestBody());
            $response = $request->getBody()->getContents();
        } catch (ClientException $e) {
            $response = json_decode($e->getResponse()->getBody()->getContents(), true);

            $Response = new Response($response);

            // Validation exception
            if ($e->getCode() === StatusCode\All::UNPROCESSABLE_ENTITY) {
                throw new ValidationException(
                    $response['message'],
                    $this,
                    $Response,
                    $response['errors'],
                    $e->getCode(),
                    $e
                );
            }

            // Generic exception
            throw new RequestException(
                $response['message'],
                $this,
                $Response,
                $e->getCode(),
                $e
            );
        } finally {
            $this->resetRoute();
        }

        return new Response($response);
    }

    /**
     * GET Request Yampi.
     *
     * @param string $route
     * @param array $queryParams
     * @throws RequestException if anything gets wrong.
     * @return Response
     */
    public function get(array $queryParams = []) : Response
    {
        return $this->request('GET', $this->route, $queryParams);
    }

    /**
     * POST Request Yampi.
     *
     * @param array $body
     * @throws RequestException if anything gets wrong.
     * @return Response
     */
    public function post(array $body = []) : Response
    {
        return $this->request('POST', $this->route, $body);
    }

    /**
     * PUT Request Yampi.
     *
     * @param array $body
     * @throws RequestException if anything gets wrong.
     * @return Response
     */
    public function put(array $body = []) : Response
    {
        return $this->request('PUT', $this->route, $body);
    }

    /**
     * PATCH Request Yampi.
     *
     * @param array $body
     * @throws RequestException if anything gets wrong.
     * @return Response
     */
    public function patch(array $body = []) : Response
    {
        return $this->request('PATCH', $this->route, $body);
    }

    /**
     * DELETE Request Yampi.
     *
     * @param array $body
     * @throws RequestException if anything gets wrong.
     * @return Response
     */
    public function delete(array $queryParams = []) : Response
    {
        return $this->request('PATCH', $this->route, $queryParams);
    }

    /**
     * Generic add a query Param for search
     *
     * @param string $key Key to add
     * @param array|string $parameter Values to add
     * @param bool $append Append to existing include
     * @return self
     */
    protected function genericSearch($key, $parameter, $append = true) : self
    {
        $values = [];

        if ($append && array_key_exists($key, $this->query)) {
            $values[] = $this->query[$key];
        }

        foreach ($parameter as $field => $value) {
            if (!is_string($value) || empty($value)) {
                throw new InvalidSearchValueException('Search value must be a not empty string', $this);
            }

            $values[] = $field . ':' . $value;
        }

        $this->setQuery($key, implode(';', $values));

        return $this;
    }

    /**
     * Return a new instance configured with production endpoint
     *
     * @return static
     */
    public static function production()
    {
        return static::url('https://api.dooki.com.br');
    }

    /**
     * Return a new instance configured with sandbox endpoint
     *
     * @return static
     */
    public static function sandbox()
    {
        return static::url('https://api-sandbox.dooki.com.br');
    }

    /**
     * Return a new instance configured with local endpoint
     *
     * @return static
     */
    public static function local()
    {
        return static::url('http://api.test');
    }

    /**
     * Return a new instance configured with local endpoint
     *
     * @return static
     */
    public static function url($url)
    {
        return new static($url);
    }
}
