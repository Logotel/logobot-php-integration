<?php

namespace Logotel\Logobot;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ServerException;
use Logotel\Logobot\Exceptions\DataInvalidException;
use Logotel\Logobot\Exceptions\InvalidResponseException;
use Logotel\Logobot\Exceptions\S3UploadFileException;
use Logotel\Logobot\Exceptions\S3UploadFileFailureException;
use Logotel\Logobot\Validator\Validator;

class BulkUploadManager extends AbstractManager
{
    protected ?ClientInterface $s3_client;

    protected string $api_uri_presigned = "/api/v1/integration/bulk-importer/presigned-url";

    protected string $api_uri_import = "/api/v1/integration/bulk-importer/import";

    protected string $file_path;
    public function __construct()
    {
        parent::__construct();

        $this->api_key = "";
        $this->file_path = "";
    }


    public function setS3Client(ClientInterface $client): self
    {

        $this->s3_client = $client;

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
    public function makeRequest(): bool|array
    {

        $this->validateData();

        $presigned_url = $this->getPresignedUrl();

        $this->uploadFile($presigned_url);

        $this->sendFile();

        return true;
    }

    public function upload(): bool
    {
        return $this->makeRequest();
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


    protected function data(): array
    {

        return [
            'api_base_url' => $this->api_base_url,
            'api_key' => $this->api_key,
            'file_path' => $this->file_path,
        ];

    }

    protected function validateData(): bool
    {

        $val = new Validator($this->data());
        $val->field('api_key')->required();
        $val->field('file_path')->required();

        if (!$val->is_valid()) {
            throw new DataInvalidException($val->displayErrors());
        }

        return true;
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
