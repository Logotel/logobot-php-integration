<?php

namespace Logotel\Logobot;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Logotel\Logobot\Exceptions\DataInvalidException;
use Logotel\Logobot\Exceptions\InvalidResponseException;
use Logotel\Logobot\Validator\Validator;

class RevokeTokenManager extends AbstractManager
{
    protected string $api_uri = "/api/v1/token/revoke";

    /**
     * The token to revoke: the JWT or the key returned by the authenticate endpoint
     */
    protected string $jwt;

    public function setJwt(string $jwt): self
    {
        $this->jwt = $jwt;

        return $this;
    }

    public function getCompleteUrl(): string
    {
        return $this->api_base_url . $this->api_uri;
    }

    /**
     * Revoke the token.
     *
     * Returns true when the token has been revoked (204), false when it was
     * already unusable (403: already revoked, expired or invalid).
     * In both cases the token can no longer be used.
     *
     * @throws DataInvalidException
     * @throws InvalidResponseException when the token cannot be revoked (e.g. 422 missing jti, 429 rate limit)
     */
    public function revoke(): bool
    {
        $data = $this->makeRequest();

        return $data['revoked'];
    }

    /**
     * @return array{status: int, revoked: bool}
     * @throws DataInvalidException
     * @throws InvalidResponseException
     */
    public function makeRequest(): array
    {
        $this->validateData();

        try {
            $response = $this->client()->request('POST', $this->getCompleteUrl(), [
                'http_errors' => false,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->jwt,
                    'Accept' => 'application/json',
                ],
            ]);
        } catch (RequestException $th) {
            throw new InvalidResponseException("Error while revoking token: " . $th->getMessage());
        }

        $status = $response->getStatusCode();

        if ($status === 204) {
            return ['status' => $status, 'revoked' => true];
        }

        if ($status === 403) {
            return ['status' => $status, 'revoked' => false];
        }

        $body = json_decode((string) $response->getBody(), true);
        $message = is_array($body) ? ($body['message'] ?? $body['error'] ?? null) : null;

        throw new InvalidResponseException($message ?? "Error while revoking token: error " . $status);
    }

    public function client(): ClientInterface
    {
        return isset($this->client) ? $this->client : new \GuzzleHttp\Client();
    }

    protected function data(): array
    {
        return [
            'jwt' => $this->jwt ?? null,
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
