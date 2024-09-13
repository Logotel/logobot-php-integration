<?php

namespace Logotel\Logobot;

use GuzzleHttp\ClientInterface;

abstract class AbstractManager
{
    protected string $api_key;
    protected ?ClientInterface $client;
    protected string $api_base_url;

    public function __construct()
    {
        $this->api_base_url = "https://chatbot.logotel.cloud";
    }

    public function setClient(ClientInterface $client)
    {

        $this->client = $client;

        return $this;
    }

    public function setApiUrl(string $api_base_url)
    {
        $this->api_base_url = $api_base_url;

        return $this;
    }

    public function setApiKey(string $api_key)
    {
        $this->api_key = $api_key;

        return $this;
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

    abstract public function makeRequest(): bool|array;
}
