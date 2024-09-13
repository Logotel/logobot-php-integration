<?php

namespace Logotel\Logobot\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Logotel\Logobot\BulkUploadManager;
use Logotel\Logobot\Exceptions\DataInvalidException;
use Logotel\Logobot\Exceptions\InvalidResponseException;
use Logotel\Logobot\Exceptions\S3UploadFileException;
use Logotel\Logobot\Manager;
use PHPUnit\Framework\TestCase;

class BulkUploadManagerTest extends TestCase
{
    public function test_class_is_returned_correctly()
    {
        $class = Manager::bulkImporter();

        $this->assertInstanceOf(BulkUploadManager::class, $class);
    }

    /**
     * @dataProvider http_cases
     */
    public function test_upload_is_ok(
        string $api_key,
        string $file_path,
        int $status_code_presigned_url,
        array $response_message_presigned_url,
        int $status_code_import,
        array $response_message_import,
        int $status_code_upload,
        ?string $thows
    ) {

        $mock = new MockHandler([
            new Response($status_code_presigned_url, [], json_encode($response_message_presigned_url)),
            new Response($status_code_import, [], json_encode($response_message_import)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $clientMock = new Client(['handler' => $handlerStack]);

        $mock_import = new MockHandler([
            new Response($status_code_upload, [], ""),
        ]);

        $handlerStackImport = HandlerStack::create($mock_import);
        $clientMockImport = new Client(['handler' => $handlerStackImport]);

        $manager = Manager::bulkImporter()->setClient($clientMock)->setS3Client($clientMockImport);

        if($thows) {
            $this->expectException($thows);
        }

        $status = $manager
            ->setApiKey($api_key)
            ->setFilePath(__DIR__ . "/fixtures/{$file_path}")
            ->upload();

        $this->assertEquals(['status' => true], $status);

    }

    public static function http_cases(): array
    {
        return [
            "with invalid valid data" => [
                "api_key" => "",
                "file_path" => "none.zip",
                "status_code_presigned_url" => 200,
                "response_message_presigned_url" => [
                    "url" => "https://a-valid-url"
                ],
                "status_code_import" => 200,
                "response_message_import" => [
                    "status" => true
                ],
                "status_code_upload" => 200,
                "throws" => DataInvalidException::class
            ],
            "with valid file" => [
                "api_key" => "12456",
                "file_path" => "success_import.zip",
                "status_code_presigned_url" => 200,
                "response_message_presigned_url" => [
                    "url" => "https://a-valid-url"
                ],
                "status_code_import" => 200,
                "response_message_import" => [
                    "status" => true
                ],
                "status_code_upload" => 200,
                "throws" => null
            ],
            "with presigned url error" => [
                "api_key" => "12456",
                "file_path" => "success_import.zip",
                "status_code_presigned_url" => 500,
                "response_message_presigned_url" => [
                    "error" => "An error occurred"
                ],
                "status_code_import" => 200,
                "response_message_import" => [
                    "status" => true
                ],
                "status_code_upload" => 200,
                "throws" => InvalidResponseException::class
            ],
            "with upload error" => [
                "api_key" => "12456",
                "file_path" => "success_import.zip",
                "status_code_presigned_url" => 200,
                "response_message_presigned_url" => [
                    "url" => "https://a-valid-url"
                ],
                "status_code_import" => 200,
                "response_message_import" => [
                    "status" => true
                ],
                "status_code_upload" => 500,
                "throws" => S3UploadFileException::class
            ],
            "with send file error" => [
                "api_key" => "12456",
                "file_path" => "success_import.zip",
                "status_code_presigned_url" => 200,
                "response_message_presigned_url" => [
                    "url" => "https://a-valid-url"
                ],
                "status_code_import" => 500,
                "response_message_import" => [
                    "error" => "An error description"
                ],
                "status_code_upload" => 200,
                "throws" => InvalidResponseException::class
            ],


        ];
    }

}
