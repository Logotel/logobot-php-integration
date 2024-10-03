<?php

namespace Logotel\Logobot\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Logotel\Logobot\Exceptions\DataInvalidException;
use Logotel\Logobot\Exceptions\InvalidResponseException;
use Logotel\Logobot\Manager;
use Logotel\Logobot\SearchEngineManager;
use PHPUnit\Framework\TestCase;

class SearchEngineManagerTest extends TestCase
{
    public function test_class_is_returned_correctly()
    {
        $class = Manager::searchEngine();

        $this->assertInstanceOf(SearchEngineManager::class, $class);
    }

    /**
     * @dataProvider http_cases
     */
    public function test_upload_is_ok(bool $generate_jwt, array $data, int $status_code, array $response_message, ?string $thows)
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

        $manager = Manager::searchEngine()->setClient($clientMock);

        if ($thows) {
            $this->expectException($thows);
        }

        $manager
            ->setApiKey($data["api_key"])
            ->setJwt($jwt)
            ->setQuery($data["query"]);

        if (isset($data["limit"])) {
            $manager->setLimit($data["limit"]);
        }

        if (isset($data["filters"])) {
            $manager->setFilters($data["filters"]);
        }

        $status = $manager->search();

        $this->assertEquals($response_message, $status);

    }

    public static function http_cases(): array
    {
        return [
            "with valid payload" => [
                "generate_jwt" => true,
                "data" => [
                    "api_key" => "123456",
                    "query" => "something i want to search",
                    "limit" => 5,
                    "filters" => [
                        "date_from" => "2022-10-10 10:10:10",
                    ],
                ],
                "status_code" => 200,
                "response_message" => [
                    [
                        'uuid' => "21b988e6-ea0f-485f-985a-f04ae60ecb61",
                        'name' => "First document",
                        'icon' => "file-pdf",
                        'distance' => 0.1,
                        'created_at' => "10/10/2024 10:10:10",
                    ],
                    [
                        'uuid' => "d2c83034-0280-48b1-9ae7-3685d8a06b9e",
                        'name' => "Second document",
                        'icon' => "file-pdf",
                        'distance' => 0.2,
                        'created_at' => "10/10/2024 10:10:10",
                    ],
                    [
                        'uuid' => "26d023fc-6298-4538-8dfe-854c5aed0b69",
                        'name' => "Third document",
                        'icon' => "file-pdf",
                        'distance' => 0.3,
                        'created_at' => "10/10/2024 10:10:10",
                    ],
                    [
                        'uuid' => "50910206-c4bf-4d86-ad39-a884e35499fa",
                        'name' => "Fourth document",
                        'icon' => "file-pdf",
                        'distance' => 0.4,
                        'created_at' => "10/10/2024 10:10:10",
                    ],
                    [
                        'uuid' => "847b3175-e2ad-4789-88d2-4a8c96f3e890",
                        'name' => "Fifth document",
                        'icon' => "file-pdf",
                        'distance' => 0.5,
                        'created_at' => "10/10/2024 10:10:10",
                    ],
                ],
                "throws" => null,
            ],
            "with invalid payload" => [
                "generate_jwt" => true,
                "data" => [
                    "api_key" => "123456",
                    "query" => "",
                ],
                "status_code" => 200,
                "response_message" => [
                    "status" => true,
                ],
                "throws" => DataInvalidException::class,
            ],
            "without jwt" => [
                "generate_jwt" => false,
                "data" => [
                    "api_key" => "123456",
                    "query" => "something i want to search",
                    "limit" => 5,
                ],
                "status_code" => 403,
                "response_message" => [
                    "status" => true,
                ],
                "throws" => \GuzzleHttp\Exception\ClientException::class,
            ],
            "with http error" => [
                "generate_jwt" => true,
                "data" => [
                    "api_key" => "123456",
                    "query" => "something i want to search",
                    "limit" => 5,
                ],
                "status_code" => 500,
                "response_message" => [

                ],
                "throws" => InvalidResponseException::class,
            ],
        ];
    }
}
