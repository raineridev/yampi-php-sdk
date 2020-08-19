<?php

namespace Tests\Unit;

use Yampi\Api\Request;
use PHPUnit\Framework\TestCase;
use Throwable;
use Yampi\Api\Exceptions\InvalidIncludeException;
use Yampi\Api\Exceptions\InvalidMethodException;
use Yampi\Api\Exceptions\InvalidParamException;
use Yampi\Api\Exceptions\InvalidSearchValueException;

class RequestTest extends TestCase
{
    public function test_instance()
    {
        $this->assertInstanceOf(
            Request::class,
            Request::production()
        );
        $this->assertInstanceOf(
            Request::class,
            Request::sandbox()
        );
        $this->assertInstanceOf(
            Request::class,
            Request::local()
        );
        $this->assertInstanceOf(
            Request::class,
            new Request('http://local.test')
        );
    }

    public function test_version()
    {
        $this->assertIsString(Request::local()->getVersion());
    }

    public function test_user_agent()
    {
        $this->assertIsString(Request::local()->getUserAgent());
    }

    public function test_custom_url()
    {
        $api = Request::url('http://local.test');

        $this->assertEquals($api->getUrl(), 'http://local.test');
        $this->assertNotEquals($api->getUrl(), 'http://wrong-url.test');

        // test if it removes the backslash
        $this->assertEquals(
            Request::url('http://local.test/')->getUrl(),
            'http://local.test'
        );

        $this->assertEquals(
            Request::url('http://local.test/////')->getUrl(),
            'http://local.test'
        );
    }

    public function test_route()
    {
        $api = Request::url('http://local.test');

        $api->setRoute('some-route');
        $this->assertEquals($api->getRoute(), 'some-route');

        $api->setRoute('');
        $this->assertEquals($api->getRoute(), '');
    }

    public function test_query()
    {
        $api = Request::url('http://local.test');

        $this->assertEmpty($api->getQuery());

        $api->setQuery('some-query', 'value');
        $this->assertNotEmpty($api->getQuery());
        $this->assertArrayHasKey('some-query', $api->getQuery());
        $this->assertEquals('value', $api->getQuery()['some-query']);

        $api->addQueries([
            'query-1' => 'value1',
            'query-2' => 'value2',
            'query-3' => 'value3',
        ]);
        $this->assertArrayHasKey('query-1', $api->getQuery());
        $this->assertArrayHasKey('query-2', $api->getQuery());
        $this->assertArrayHasKey('query-3', $api->getQuery());
        $this->assertEquals('value1', $api->getQuery()['query-1']);
        $this->assertEquals('value2', $api->getQuery()['query-2']);
        $this->assertEquals('value3', $api->getQuery()['query-3']);

        $api->clearQuery();
        $this->assertEmpty($api->getQuery());
    }

    public function test_merchant()
    {
        $api = Request::url('http://local.test');
        $api->setMerchant('merchantalias');

        $this->assertEquals($api->getFullRoute(), 'http://local.test/v2/merchantalias');

        $api->setRoute('auth');
        $this->assertEquals($api->getFullRoute(), 'http://local.test/v2/auth');

        $api->setRoute('auth')->forceAlias();
        $this->assertEquals($api->getFullRoute(), 'http://local.test/v2/merchantalias/auth');

        $api->setRoute('some-route')->forgetAlias();
        $this->assertEquals($api->getFullRoute(), 'http://local.test/v2/some-route');
    }

    public function test_set_method()
    {
        $this->assertInstanceOf(
            Request::class,
            Request::local()->setMethod('GET')
        );

        try {
            Request::local()->setMethod('INVALID');
            $this->fail('Exception not thrown');
        } catch (Throwable $e) {
            $this->assertInstanceOf(InvalidMethodException::class, $e);
            $this->assertNotNull($e->getRequest());
        }
    }

    public function test_set_param()
    {
        $api = Request::local();

        $api->setParam('headers', 'test-header', 'value');
        $this->assertArrayHasKey('test-header', $api->getHeaders());
        $this->assertEquals('value', $api->getHeaders()['test-header']);

        $api->setParam('query', 'test-query', 'value');
        $this->assertArrayHasKey('test-query', $api->getQuery());
        $this->assertEquals('value', $api->getQuery()['test-query']);

        $api->setParam('body', 'test-body', 'value');
        $this->assertArrayHasKey('test-body', $api->getBody());
        $this->assertEquals('value', $api->getBody()['test-body']);

        $this->expectException(InvalidParamException::class);
        $api->setParam('invalid', 'invalid', 'invalid');
    }

    public function test_body()
    {
        $api = Request::local();
        $body = $api->getBody();

        $this->assertEmpty($body);

        $api->addBody([
            'some-body' => 'some-value',
        ]);
        $body = $api->getBody();

        $this->assertNotEmpty($body);
        $this->assertArrayHasKey('some-body', $body);
        $this->assertEquals('some-value', $body['some-body']);

        $api->addBody([
            'new-body' => 'new-value',
        ]);
        $body = $api->getBody();
        $this->assertArrayHasKey('some-body', $body);
        $this->assertArrayHasKey('new-body', $body);
        $this->assertEquals('some-value', $body['some-body']);
        $this->assertEquals('new-value', $body['new-body']);

        $api->setBody([
            'empty-body' => 'new-value',
        ]);
        $body = $api->getBody();
        $this->assertArrayHasKey('empty-body', $body);
        $this->assertArrayNotHasKey('some-body', $body);
        $this->assertArrayNotHasKey('new-body', $body);
    }

    public function test_request_body()
    {
        $api = Request::local();
        $body = $api->getRequestBody();
        $this->assertArrayHasKey('headers', $body);
        $this->assertArrayHasKey('query', $body);

        $api->setMethod('GET');
        $body = $api->getRequestBody();
        $this->assertArrayHasKey('headers', $body);
        $this->assertArrayHasKey('query', $body);
        $this->assertArrayNotHasKey('json', $body);

        $api->setMethod('POST');
        $body = $api->getRequestBody();
        $this->assertArrayHasKey('headers', $body);
        $this->assertArrayHasKey('query', $body);
        $this->assertArrayHasKey('json', $body);
    }

    public function test_header()
    {
        $api = Request::url('http://local.test');
        $headers = $api->getRequestBody()['headers'];
        $this->assertArrayHasKey('User-Agent', $headers);

        $api->addHeader('Some-Header', 'Some-Value');

        $this->assertArrayHasKey('Some-Header', $api->getHeaders());
        $this->assertArrayNotHasKey('Some-Invalid-Header', $api->getHeaders());
        $this->assertEquals('Some-Value', $api->getHeaders()['Some-Header']);

        $headers = $api->getRequestBody()['headers'];
        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertArrayHasKey('Some-Header', $headers);
        $this->assertEquals('Some-Value', $headers['Some-Header']);
    }

    public function test_custom_route()
    {
        $api = Request::url('http://local.test');

        $api->catalog();
        $this->assertEquals($api->getRoute(), 'catalog');
        $this->assertEquals($api->getFullRoute(), 'http://local.test/v2/catalog');

        $api->products(123);
        $this->assertEquals($api->getRoute(), 'catalog/products/123');
        $this->assertEquals($api->getFullRoute(), 'http://local.test/v2/catalog/products/123');

        $api->resetRoute()
            ->catalog()
            ->products(123);
        $this->assertEquals($api->getRoute(), 'catalog/products/123');
        $this->assertEquals($api->getFullRoute(), 'http://local.test/v2/catalog/products/123');
    }

    public function test_include()
    {
        $api = Request::url('http://local.test');

        $this->assertArrayNotHasKey('include', $api->getQuery());

        $api->include(['sku']);
        $this->assertArrayHasKey('include', $api->getQuery());
        $this->assertEquals('sku', $api->getQuery()['include']);

        $api->include('services');
        $this->assertEquals('sku,services', $api->getQuery()['include']);

        $api->includes('test');
        $this->assertEquals('sku,services,test', $api->getQuery()['include']);

        $api->includes(['test2']);
        $this->assertEquals('sku,services,test,test2', $api->getQuery()['include']);

        $api->includes(['clean'], false);
        $this->assertEquals('clean', $api->getQuery()['include']);

        $this->expectException(InvalidIncludeException::class);
        $api->include(false);
        $api->include(['']);
    }

    public function test_search()
    {
        $this->genericTestSearch('search');
    }

    public function test_search_params()
    {
        $this->genericTestSearch('searchFields');
    }

    public function test_order_by()
    {
        $api = Request::url('http://local.test');

        $this->assertArrayNotHasKey('orderBy', $api->getQuery());

        // must not break if called before any sortBy
        $api->noOrderBy();
        $this->assertArrayNotHasKey('orderBy', $api->getQuery());

        $api->orderBy('name');
        $this->assertArrayHasKey('orderBy', $api->getQuery());
        $this->assertEquals('name', $api->getQuery()['orderBy']);

        $api->orderBy('id');
        $this->assertEquals('id', $api->getQuery()['orderBy']);

        $api->noOrderBy();
        $this->assertArrayNotHasKey('orderBy', $api->getQuery());
    }

    public function test_sort_by()
    {
        $api = Request::url('http://local.test');

        $this->assertArrayNotHasKey('sortedBy', $api->getQuery());

        // must not break if called before any sortBy
        $api->noSortBy();
        $this->assertArrayNotHasKey('sortedBy', $api->getQuery());

        $api->sortBy('asc');
        $this->assertArrayHasKey('sortedBy', $api->getQuery());
        $this->assertEquals('asc', $api->getQuery()['sortedBy']);

        $api->sortedBy('desc');
        $this->assertEquals('desc', $api->getQuery()['sortedBy']);

        $api->noSortBy();
        $this->assertArrayNotHasKey('sortedBy', $api->getQuery());
    }

    public function test_period()
    {
        $api = Request::url('http://local.test');

        $this->assertArrayNotHasKey('date', $api->getQuery());

        // must not break if called before any sortBy
        $api->noPeriod();
        $this->assertArrayNotHasKey('date', $api->getQuery());

        $api->period('2020-01-01', '2020-01-31');
        $this->assertArrayHasKey('date', $api->getQuery());
        $this->assertEquals('created_at:2020-01-01|2020-01-31', $api->getQuery()['date']);

        $api->period('2020-02-01', '2020-02-31', 'updated_at');
        $this->assertEquals('updated_at:2020-02-01|2020-02-31', $api->getQuery()['date']);

        $api->period('2020-02-01', '2020-02-31');
        $this->assertEquals('created_at:2020-02-01|2020-02-31', $api->getQuery()['date']);

        $api->noPeriod();
        $this->assertArrayNotHasKey('date', $api->getQuery());
    }

    public function test_limit()
    {
        $api = Request::url('http://local.test');

        $this->assertArrayNotHasKey('limit', $api->getQuery());

        // must not break if called before any limit
        $api->noLimit();
        $this->assertArrayNotHasKey('limit', $api->getQuery());

        $api->limit(10);
        $this->assertArrayHasKey('limit', $api->getQuery());
        $this->assertEquals(10, $api->getQuery()['limit']);

        $api->limit(20);
        $this->assertEquals(20, $api->getQuery()['limit']);

        $api->noLimit();
        $this->assertArrayNotHasKey('limit', $api->getQuery());
    }

    public function test_page()
    {
        $api = Request::url('http://local.test');

        $this->assertArrayNotHasKey('page', $api->getQuery());

        // must not break if called before any page
        $api->noPage();
        $this->assertArrayNotHasKey('page', $api->getQuery());

        $api->page(1);
        $this->assertArrayHasKey('page', $api->getQuery());
        $this->assertEquals(1, $api->getQuery()['page']);

        $api->page(2);
        $this->assertEquals(2, $api->getQuery()['page']);

        $api->noPage();
        $this->assertArrayNotHasKey('page', $api->getQuery());
    }

    public function test_skip_cache()
    {
        $api = Request::url('http://local.test');

        $this->assertArrayNotHasKey('skipCache', $api->getQuery());

        $api->skipCache();
        $this->assertArrayHasKey('skipCache', $api->getQuery());
        $this->assertEquals('true', $api->getQuery()['skipCache']);
    }

    protected function genericTestSearch($key)
    {
        $api = Request::url('http://local.test');

        $this->assertArrayNotHasKey($key, $api->getQuery());

        $api->{$key}(['name' => 'Lucas Collete']);
        $this->assertArrayHasKey($key, $api->getQuery());
        $this->assertEquals('name:Lucas Collete', $api->getQuery()[$key]);

        $api->{$key}(['some' => 'thing']);
        $this->assertEquals('name:Lucas Collete;some:thing', $api->getQuery()[$key]);

        $api->{$key}(['new' => 'search'], false);
        $this->assertEquals('new:search', $api->getQuery()[$key]);

        $this->expectException(InvalidSearchValueException::class);
        $api->{$key}(['invalid' => '']);
        $api->{$key}(['invalid' => []]);
    }
}
