<?php

namespace Logotel\Logobot;

use GuzzleHttp\Exception\ServerException;
use Logotel\Logobot\Exceptions\DataInvalidException;
use Logotel\Logobot\Exceptions\InvalidResponseException;
use Logotel\Logobot\Validator\Validator;

class SearchEngineManager extends AbstractManager
{
    protected string $api_uri = "/api/v1/search-engine/documents";

    protected string $query;

    protected ?int $limit = null;

    protected string $jwt;

    public function setJwt(string $jwt): self
    {

        $this->jwt = $jwt;

        return $this;
    }

    public function setQuery(string $query): self
    {

        $this->query = $query;

        return $this;
    }

    public function setLimit(int $limit): self
    {

        $this->limit = $limit;

        return $this;
    }

    public function getCompleteUrl(): string
    {
        return $this->api_base_url . $this->api_uri;
    }

    public function search(): array
    {
        return $this->makeRequest();
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
                    'question' => $this->query,
                    'limit' => $this->limit
                ]
            );
        } catch (ServerException $th) {
            if (!$th->hasResponse()) {
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
            'query' => $this->query,
            'limit' => $this->limit,
            'jwt' => $this->jwt,
        ];
    }

    protected function validateData(): bool
    {

        $val = new Validator($this->data());
        $val->field('query')->required()->max_len(500);
        $val->field('jwt')->required();
        $val->field('limit')->numeric();

        if (!$val->is_valid()) {
            throw new DataInvalidException($val->displayErrors());
        }

        return true;
    }
}
