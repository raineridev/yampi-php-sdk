<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

use Yampi\Api\AuthRequest;

class AuthRequestTest extends TestCase
{
    public function test_instance()
    {
        $this->assertInstanceOf(
            AuthRequest::class,
            AuthRequest::production()
        );
        $this->assertInstanceOf(
            AuthRequest::class,
            AuthRequest::sandbox()
        );
        $this->assertInstanceOf(
            AuthRequest::class,
            AuthRequest::local()
        );
        $this->assertInstanceOf(
            AuthRequest::class,
            new AuthRequest('http://local.test')
        );
    }

    public function test_user_token()
    {
        $api = AuthRequest::local();

        $this->assertEmpty($api->getAuthToken());
        $this->assertEmpty($api->getAuthTokenType());

        $api->setUserToken('user-token-example');
        $this->assertEquals('user-token-example', $api->getAuthToken());
        $this->assertEquals('user-token', $api->getAuthTokenType());
        $this->assertArrayHasKey('User-Token', $api->getHeaders());

        $api->setJwt('jwt-example');
        $this->assertEquals('jwt-example', $api->getAuthToken());
        $this->assertEquals('bearer', $api->getAuthTokenType());
        $this->assertArrayHasKey('Authorization', $api->getHeaders());
    }
}
