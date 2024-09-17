<?php

namespace Logotel\Logobot;

use GuzzleHttp\Exception\ServerException;
use Logotel\Logobot\Exceptions\CannotDeleteFileException;
use Logotel\Logobot\Exceptions\InvalidResponseException;

class DeleteDocumentManager extends AbstractManager
{
    protected string $api_uri_delete = "/api/v1/integration/bulk-importer/delete-document";

    protected string $identifier;

    public function makeRequest(): array
    {
        try {
            /** @var \GuzzleHttp\Client */
            $client = $this->client();

            $response = $client->post(
                $this->getCompleteUrl(),
                [
                    'json' => [
                        'identifier' => $this->getIdentifier(),
                    ],
                ]
            );
        } catch (ServerException $th) {
            if (! $th->hasResponse()) {
                throw new InvalidResponseException("Generic server error");
            }

            $response = $th->getResponse();

            throw new InvalidResponseException(json_decode($response->getBody()->getContents(), true)['error']);
        }

        if ($response->getStatusCode() !== 200) {
            throw new InvalidResponseException(json_decode($response->getBody()->getContents(), true)['error'] ?? "Error while deleting document: error " . $response->getStatusCode());
        }

        $status = json_decode($response->getBody()->getContents(), true)['status'] ?? false;

        if (! $status) {
            throw new CannotDeleteFileException(json_decode($response->getBody()->getContents(), true)['error'] ?? "Cannot delete file");
        }

        return ["status" => true];
    }

    public function delete(): array
    {
        return $this->makeRequest();
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getIdentifier(): string
    {
        return $this->identifier ? (string)$this->identifier : uniqid();
    }

    public function getCompleteUrl(): string
    {
        return $this->api_base_url . $this->api_uri_delete;
    }
}
