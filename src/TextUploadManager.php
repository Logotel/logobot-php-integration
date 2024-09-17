<?php

namespace Logotel\Logobot;

use GuzzleHttp\Exception\ServerException;
use Logotel\Logobot\Exceptions\DataInvalidException;
use Logotel\Logobot\Exceptions\InvalidResponseException;
use Logotel\Logobot\Validator\Validator;

class TextUploadManager extends AbstractManager
{
    protected string $api_uri = "/api/v1/integration/bulk-importer/import-texts";
    protected string $identifier = "";

    protected string $content = "";

    protected string $link = "";

    protected string $title = "";

    protected string $language = "";

    protected array $permissions = [];

    protected ?array $metadata = [];

    protected string $document_date = "";

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
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

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setDocumentDate(string $document_date): self
    {
        $this->document_date = $document_date;

        return $this;
    }

    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function setMetadata(?array $metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Upload the content to the bot service
     *
     * @return array
     * @throws DataInvalidException
     * @throws InvalidResponseException
     */
    public function makeRequest(): array
    {

        $this->validateData();

        try {

            /** @var \GuzzleHttp\Client */
            $client = $this->client();

            $response = $client->post(
                $this->getCompleteUrl(),
                [
                    'json' => [
                        'data' => [
                            [
                                'identifier' => $this->getIdentifier(),
                                'title' => $this->title,
                                'link' => $this->link,
                                'language' => $this->language,
                                'content' => $this->content,
                                'permissions' => $this->permissions,
                                'metadata' => $this->metadata,
                                'document_date' => $this->document_date,
                            ],
                        ],
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
            throw new InvalidResponseException(json_decode($response->getBody()->getContents(), true)['error'] ?? "Error while sending data: error " . $response->getStatusCode());
        }

        return ["status" => true];
    }

    public function upload(): array
    {
        return $this->makeRequest();
    }

    public function getCompleteUrl(): string
    {
        return $this->api_base_url . $this->api_uri;
    }

    protected function data(): array
    {

        return [
            'api_uri' => $this->api_uri,
            'api_base_url' => $this->api_base_url,
            'api_key' => $this->api_key,
            'identifier' => $this->identifier,
            'content' => $this->content,
            'link' => $this->link,
            'title' => $this->title,
            'language' => $this->language,
            'permissions' => $this->permissions,
            'metadata' => $this->metadata,
            'document_date' => $this->document_date,
        ];
    }

    protected function validateData(): bool
    {

        $val = new Validator($this->data());
        $val->field('api_key')->required();
        $val->field('identifier')->required();
        $val->field('title')->required();
        $val->field('link')->required();
        $val->field('language')->min_len(2)->max_len(2)->required();
        $val->field('content')->required();
        $val->field('permissions')->array()->required();
        $val->field('metadata')->array();
        $val->field('document_date')->required();

        if (! $val->is_valid()) {
            throw new DataInvalidException($val->displayErrors());
        }

        return true;
    }
}
