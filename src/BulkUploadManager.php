<?php

namespace Logotel\Logobot;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ServerException;
use Logotel\Logobot\Exceptions\DataInvalidException;
use Logotel\Logobot\Exceptions\InvalidResponseException;
use Logotel\Logobot\Exceptions\S3UploadFileException;
use Logotel\Logobot\Exceptions\S3UploadFileFailureException;
use Logotel\Logobot\Validator\Validation;

class BulkUploadManager
{
    protected ?ClientInterface $client;

    protected ?ClientInterface $s3_client;

    protected string $api_uri_presigned = "/api/v1/integration/bulk-importer/presigned-url";

    protected string $api_uri_import = "/api/v1/integration/bulk-importer/import";

    protected string $api_base_url;

    protected string $api_key;

    protected string $file_path;
    public function __construct()
    {
        $this->api_base_url = "https://chatbot.logotel.cloud";

        $this->api_key = "";
        $this->file_path = "";
    }

    public function setClient(ClientInterface $client): self
    {

        $this->client = $client;

        return $this;
    }

    public function setS3Client(ClientInterface $client): self
    {

        $this->s3_client = $client;

        return $this;
    }

    public function setApiUrl(string $api_base_url): self
    {
        $this->api_base_url = $api_base_url;

        return $this;
    }

    public function setApiKey(string $api_key): self
    {
        $this->api_key = $api_key;

        return $this;
    }

    public function setFilePath(string $file_path): self
    {
        $this->file_path = $file_path;

        return $this;
    }

    /**
     * Upload the content to the bot service
     *
     * @return boolean
     * @throws DataInvalidException
     * @throws InvalidResponseException
     */
    public function upload(): bool
    {

        $this->validateData();

        $presigned_url = $this->getPresignedUrl();

        $this->uploadFile($presigned_url);

        $this->sendFile();

        return true;
    }

    protected function sendFile(): bool
    {
        try {

            /** @var \GuzzleHttp\Client */
            $client = $this->client();

            $response = $client->post($this->getCompleteUrlImport());
        } catch (ServerException $th) {
            if (!$th->hasResponse()) {
                throw new InvalidResponseException("Generic server error");
            }

            $response = $th->getResponse();

            throw new InvalidResponseException(json_decode($response->getBody()->getContents(), true)['error']);
        }

        if ($response->getStatusCode() !== 200) {
            throw new InvalidResponseException(json_decode($response->getBody()->getContents(), true)['error'] ?? "Error while sending data: error " . $response->getStatusCode());
        }

        return true;
    }

    protected function uploadFile(string $presigned_url): void
    {

        /** @var \GuzzleHttp\Client */
        $client = $this->s3Client();

        $fileHandle = fopen($this->file_path, 'r');



        try {

            $response = $client->put($presigned_url, [
                'body' => $fileHandle,
                'headers' => [
                    'Content-Type' => 'application/octet-stream', // Tipo di contenuto
                ],
            ]);


            // $s3Client = new S3Client([
            //     'version' => 'latest',
            // ]);

            // $result = $s3Client->putObject([
            //     'Bucket' => null,
            //     'Key' => "import.zip",
            //     'Body' => fopen($this->file_path, 'r'),
            //     'ContentLength' => filesize($this->file_path),
            //     '@http' => [
            //         'stream' => true,
            //     ],
            //     'SourceFile' => $this->file_path,
            //     'Endpoint' => $presigned_url,
            // ]);

        } catch (\Throwable $th) {
            throw new S3UploadFileException($th->getMessage());
        }

        if ($response->getStatusCode() !== 200) {
            throw new S3UploadFileFailureException();
        }

    }

    protected function getPresignedUrl(): string
    {

        try {

            /** @var \GuzzleHttp\Client */
            $client = $this->client();

            $response = $client->get($this->getCompleteUrlPresigned());

        } catch (ServerException $th) {
            if (!$th->hasResponse()) {
                throw new InvalidResponseException("Generic server error");
            }

            $response = $th->getResponse();

            throw new InvalidResponseException(json_decode($response->getBody()->getContents(), true)['error'] ?? "Error without valid description: " . $th->getMessage());
        }

        if ($response->getStatusCode() !== 200) {
            throw new InvalidResponseException(json_decode($response->getBody()->getContents(), true)['error'] ?? "Error while sending data: error " . $response->getStatusCode());
        }

        return json_decode($response->getBody()->getContents(), true)['url'];

    }

    public function getCompleteUrlPresigned(): string
    {
        return $this->api_base_url . $this->api_uri_presigned;
    }

    public function getCompleteUrlImport(): string
    {
        return $this->api_base_url . $this->api_uri_import;
    }

    protected function validateData(): bool
    {

        $val = new Validation();
        $val->name('api_key')->value($this->api_key ?? "")->customPattern('[A-Za-z0-9-.]+')->required();
        $val->name('file_path')->value($this->file_path ?? "")->required();

        if (!$val->isSuccess()) {
            throw new DataInvalidException($val->displayErrors());
        }

        return true;
    }

    public function client(): ClientInterface
    {
        return isset($this->client) ? $this->client : new \GuzzleHttp\Client(
            [
                'headers' => [
                    'x-api-key' => $this->api_key,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ]
            ]
        );
    }

    public function s3Client(): ClientInterface
    {
        return isset($this->s3_client) ? $this->s3_client : new \GuzzleHttp\Client(
            [
                'headers' => [
                    'Content-Type' => 'application/octet-stream'
                ]
            ]
        );
    }
}
