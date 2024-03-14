<?php

namespace Logotel\Logobot\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Logotel\Logobot\Exceptions\DataInvalidException;
use Logotel\Logobot\Exceptions\InvalidResponseException;
use Logotel\Logobot\Manager;
use Logotel\Logobot\TextUploadManager;
use PHPUnit\Framework\TestCase;

class TextUploadManagerTest extends TestCase
{
    public function test_class_is_returned_correctly()
    {
        $class = Manager::textUpload();

        $this->assertInstanceOf(TextUploadManager::class, $class);
    }

    /**
     * @dataProvider http_cases
     */
    public function test_upload_is_ok(array $data, int $status_code, array $response_message, ?string $thows)
    {

        $mock = new MockHandler([
            new Response($status_code, [], json_encode($response_message))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $clientMock = new Client(['handler' => $handlerStack]);

        $manager = Manager::textUpload()->setClient($clientMock);

        if($thows) {
            $this->expectException($thows);
        }

        $status = $manager
            ->setApiKey($data["api_key"])
            ->setTitle($data["title"])
            ->setContent($data["content"])
            ->setLink($data["link"])
            ->setPermissions($data["permissions"])
            ->setLanguage($data["language"])
            ->upload();

        $this->assertTrue($status);

    }

    public static function http_cases(): array
    {
        return [
            "with valid payload" => [
                "data" => [
                    "api_key" => "123456",
                    "title" => "a title",
                    "content" => "some text to upload",
                    "link" => "https://www.example.com",
                    "language" => "it",
                    "permissions" => ["a", "b", "c"],
                ],
                "status_code" => 200,
                "response_message" => [
                    "status" => true
                ],
                "throws" => null
            ],
            "with invalid payload" => [
                "data" => [
                    "api_key" => "",
                    "title" => "",
                    "content" => "",
                    "link" => "test",
                    "language" => "it",
                    "permissions" => ["a", "b", "c"],
                ],
                "status_code" => 200,
                "response_message" => [
                    "status" => true
                ],
                "throws" => DataInvalidException::class
            ],
            "with http error" => [
                "data" => [
                    "api_key" => "123456",
                    "title" => "a title",
                    "content" => "some text to upload",
                    "link" => "https://www.example.com",
                    "language" => "it",
                    "permissions" => ["a", "b", "c"],
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
