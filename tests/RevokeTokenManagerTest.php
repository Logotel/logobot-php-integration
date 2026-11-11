<?php

namespace Logotel\Logobot\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Logotel\Logobot\Exceptions\DataInvalidException;
use Logotel\Logobot\Exceptions\InvalidResponseException;
use Logotel\Logobot\Manager;
use Logotel\Logobot\RevokeTokenManager;
use PHPUnit\Framework\TestCase;

class RevokeTokenManagerTest extends TestCase
{
    private array $history = [];

    private function clientReturning(Response $response): Client
    {
        $this->history = [];
        $stack = HandlerStack::create(new MockHandler([$response]));
        $stack->push(Middleware::history($this->history));

        return new Client(['handler' => $stack]);
    }

    public function test_class_is_returned_correctly()
    {
        $this->assertInstanceOf(RevokeTokenManager::class, Manager::revokeToken());
    }

    public function test_revoke_sends_bearer_token_to_revoke_endpoint()
    {
        $revoked = Manager::revokeToken()
            ->setApiUrl('https://api.example.test')
            ->setJwt('my-jwt')
            ->setClient($this->clientReturning(new Response(204)))
            ->revoke();

        $this->assertTrue($revoked);
        $this->assertCount(1, $this->history);

        $request = $this->history[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('https://api.example.test/api/v1/token/revoke', (string) $request->getUri());
        $this->assertEquals('Bearer my-jwt', $request->getHeaderLine('Authorization'));
        $this->assertEquals('application/json', $request->getHeaderLine('Accept'));
    }

    public function test_already_unusable_token_returns_false()
    {
        $revoked = Manager::revokeToken()
            ->setJwt('my-jwt')
            ->setClient($this->clientReturning(new Response(403, [], json_encode(['message' => 'Invalid JWT']))))
            ->revoke();

        $this->assertFalse($revoked);
    }

    /**
     * @dataProvider failing_cases
     */
    public function test_revoke_failures_throw(int $status, array $body, string $message)
    {
        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage($message);

        Manager::revokeToken()
            ->setJwt('my-jwt')
            ->setClient($this->clientReturning(new Response($status, [], json_encode($body))))
            ->revoke();
    }

    public static function failing_cases(): array
    {
        return [
            'token without jti' => [422, ['message' => 'Token cannot be revoked'], 'Token cannot be revoked'],
            'rate limited' => [429, [], 'error 429'],
            'server error' => [500, [], 'error 500'],
        ];
    }

    public function test_revoke_without_jwt_throws()
    {
        $this->expectException(DataInvalidException::class);

        Manager::revokeToken()
            ->setClient($this->clientReturning(new Response(204)))
            ->revoke();
    }
}
