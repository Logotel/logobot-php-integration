<?php

namespace Logotel\Logobot;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ServerException;
use Logotel\Logobot\Exceptions\DataInvalidException;
use Logotel\Logobot\Exceptions\InvalidResponseException;
use Logotel\Logobot\Validator\Validation;

class TextUploadManager
{
    protected ?ClientInterface $client;

    protected string $api_uri = "/api/v1/integration/bulk-importer/import-texts";

    protected string $api_base_url;

    protected string $api_key;

    protected string $content;

    protected string $link;

    protected string $language;

    protected array $permissions;

    public function __construct()
    {
        $this->api_base_url = "https://chatbot.logotel.cloud";
    }

    public function setClient(ClientInterface $client): self
    {

        $this->client = $client;

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

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
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

    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;

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

        try {

            /** @var \GuzzleHttp\Client */
            $client = $this->client();

            $response = $client->post(
                $this->getCompleteUrl(),
                [
                    'json' => [
                        'data' => [
                            'link' => $this->link,
                            'language' => $this->language,
                            'content' => $this->content,
                            'permissions' => $this->permissions
                        ]
                    ]
                ]
            );

        } catch (ServerException $th) {
            if (!$th->hasResponse()) {
                throw new InvalidResponseException("Generic server error");
            }

            $response = $th->getResponse();

            throw new InvalidResponseException(json_decode($response->getBody()->getContents(), true)['error']);

        }

        if($response->getStatusCode() !== 200) {
            throw new InvalidResponseException(json_decode($response->getBody()->getContents(), true)['error'] ?? "Error while sending data: error " . $response->getStatusCode());
        }

        return true;

    }

    public function getCompleteUrl(): string
    {
        return $this->api_base_url . $this->api_uri;
    }

    protected function validateData(): bool
    {

        $val = new Validation();
        $val->name('api_key')->value($this->api_key ?? "")->customPattern('[A-Za-z0-9-]+')->required();
        $val->name('link')->value($this->link ?? "")->pattern('url')->required();
        $val->name('language')->value($this->language ?? "")->customPattern('[a-z]{2}')->required();
        $val->name('test')->value($this->content ?? "")->pattern('words')->required();
        $val->name('permissions')->value($this->permissions ?? null)->pattern('array')->required();

        if(!$val->isSuccess()) {
            throw new DataInvalidException($val->displayErrors());
        }

        return true;
    }

    public function client(): ClientInterface
    {
        return $this->client ?: new \GuzzleHttp\Client(
            [
                'headers' => [
                    'x-api-key' => $this->api_key,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ]
            ]
        );

    }

}
