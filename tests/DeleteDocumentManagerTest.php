<?php

namespace Logotel\Logobot\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Logotel\Logobot\DeleteDocumentManager;
use Logotel\Logobot\Exceptions\CannotDeleteFileException;
use Logotel\Logobot\Exceptions\InvalidResponseException;
use Logotel\Logobot\Manager;
use PHPUnit\Framework\TestCase;

class DeleteDocumentManagerTest extends TestCase
{
    public function test_class_is_returned_correctly()
    {
        $class = Manager::deleteDocument();

        $this->assertInstanceOf(DeleteDocumentManager::class, $class);
    }

    /**
     * @dataProvider http_cases
     */
    public function test_delete_is_ok(array $data, int $status_code, array $response_message, ?string $thows)
    {

        $mock = new MockHandler([
            new Response($status_code, [], json_encode($response_message))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $clientMock = new Client(['handler' => $handlerStack]);

        $manager = Manager::deleteDocument()->setClient($clientMock);

        if ($thows) {
            $this->expectException($thows);
        }

        $status = $manager
            ->setApiKey($data["api_key"])
            ->setIdentifier($data["identifier"])
            ->delete();

        $this->assertEquals(['status' => true], $status);
    }

    public static function http_cases(): array
    {
        return [
            "with valid identifier" => [
                "data" => [
                    "api_key" => "123456",
                    "identifier" => "identifier",
                ],
                "status_code" => 200,
                "response_message" => [
                    "status" => true
                ],
                "throws" => null
            ],
            "with invalid identifier" => [
                "data" => [
                    "api_key" => "123456",
                    "identifier" => "not exists",
                ],
                "status_code" => 200,
                "response_message" => [
                    "status" => false
                ],
                "throws" => CannotDeleteFileException::class
            ],
            "with http error" => [
                "data" => [
                    "api_key" => "123456",
                    "identifier" => "123456",
                ],
                "status_code" => 500,
                "response_message" => [
                    "error" => "Some error"
                ],
                "throws" => InvalidResponseException::class
            ],
        ];
    }
}
