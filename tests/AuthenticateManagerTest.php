<?php

namespace Logotel\Logobot\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Logotel\Logobot\AuthenticateManager;
use Logotel\Logobot\Exceptions\DataInvalidException;
use Logotel\Logobot\Exceptions\InvalidResponseException;
use Logotel\Logobot\Manager;
use PHPUnit\Framework\TestCase;

class AuthenticateManagerTest extends TestCase
{
    public function test_class_is_returned_correctly()
    {
        $class = Manager::authenticate();

        $this->assertInstanceOf(AuthenticateManager::class, $class);
    }

    /**
     * @dataProvider http_cases
     */
    public function test_auth_is_ok(bool $generate_jwt, array $data, int $status_code, array $response_message, ?string $thows)
    {

        $jwt = "something";

        if ($generate_jwt) {
            $email = 'test@email.com';
            $identifier = '12345';
            $license = 'license';
            $permissions = ['admin'];
            $is_super_user = false;

            $jwt = Manager::jwt()
                ->setKey(file_get_contents(__DIR__ . '/fixtures/private_key.txt'))
                ->setLicense($license)
                ->setEmail($email)
                ->setIdentifier($identifier)
                ->setPermissions($permissions)
                ->setIsSuperUser($is_super_user)
                ->generate();
        }

        $mock = new MockHandler([
            new Response($status_code, [], json_encode($response_message)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $clientMock = new Client(['handler' => $handlerStack]);

        $manager = Manager::authenticate()
        ->setApiKey($data["api_key"])
        ->setJwt($jwt)
        ->setClient($clientMock);

        if ($thows) {
            $this->expectException($thows);
        }

        $status = $manager->generate();

        $this->assertEquals($response_message["key"], $status);

    }

    public static function http_cases(): array
    {
        return [
            "with valid payload" => [
                "generate_jwt" => true,
                "data" => [
                    "api_key" => "123456",
                ],
                "status_code" => 200,
                "response_message" => [
                    "key" => "something",
                ],
                "throws" => null,
            ],
            // "with invalid payload" => [
            //     "generate_jwt" => true,
            //     "status_code" => 200,
            //     "response_message" => [
            //         "status" => true,
            //     ],
            //     "throws" => DataInvalidException::class,
            // ],
            // "without jwt" => [
            //     "generate_jwt" => false,
            //     "data" => [
            //         "api_key" => "123456",
            //         "query" => "something i want to search",
            //         "limit" => 5,
            //     ],
            //     "status_code" => 403,
            //     "response_message" => [
            //         "status" => true,
            //     ],
            //     "throws" => \GuzzleHttp\Exception\ClientException::class,
            // ],
            // "with http error" => [
            //     "generate_jwt" => true,
            //     "data" => [
            //         "api_key" => "123456",
            //         "query" => "something i want to search",
            //         "limit" => 5,
            //     ],
            //     "status_code" => 500,
            //     "response_message" => [

            //     ],
            //     "throws" => InvalidResponseException::class,
            // ],
        ];
    }
}
