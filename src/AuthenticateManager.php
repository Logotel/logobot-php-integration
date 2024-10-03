<?php

namespace Logotel\Logobot;

use GuzzleHttp\Exception\ServerException;
use Logotel\Logobot\Exceptions\DataInvalidException;
use Logotel\Logobot\Exceptions\InvalidResponseException;
use Logotel\Logobot\Validator\Validator;

class AuthenticateManager extends AbstractManager
{
    protected string $api_uri = "/api/v1/authenticate";

    protected string $jwt;

    public function __construct()
    {
        parent::__construct();
        $this->api_base_url = "https://api-staging.chatbot.logotel.cloud";
    }

    public function setJwt(string $jwt): self
    {

        $this->jwt = $jwt;

        return $this;
    }

    public function getCompleteUrl(): string
    {
        return $this->api_base_url . $this->api_uri;
    }

    public function generate(): string
    {
        $data = $this->makeRequest();

        return $data["key"];
    }

    /**
     * Call the api for result
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
                        'jwt' => $this->jwt,
                    ],
                    'headers' => [
                        'x-client-id' => 'logobot',
                    ]
                ]
            );
        } catch (ServerException $th) {
            if (! $th->hasResponse()) {
                throw new InvalidResponseException("Generic server error");
            }

            $response = $th->getResponse();

            throw new InvalidResponseException(json_decode($response->getBody()->getContents(), true)['error'] ?? "Error while sending data: error " . $response->getStatusCode());
        }

        if ($response->getStatusCode() !== 200) {
            throw new InvalidResponseException(json_decode($response->getBody()->getContents(), true)['error'] ?? "Error while sending data: error " . $response->getStatusCode());
        }

        try {
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Throwable $th) {
            throw new InvalidResponseException($th->getMessage());
        }

    }

    protected function data(): array
    {

        return [
            'jwt' => $this->jwt,
        ];
    }

    protected function validateData(): bool
    {

        $val = new Validator($this->data());
        $val->field('jwt')->required();

        if (! $val->is_valid()) {
            throw new DataInvalidException($val->displayErrors());
        }

        return true;
    }
}
