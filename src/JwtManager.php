<?php

namespace Logotel\Logobot;

use Firebase\JWT\JWT;
use Logotel\Logobot\Exceptions\KeyFileNotFound;
use Logotel\Logobot\Exceptions\UserInvalidException;
use Logotel\Logobot\Validator\Validator;

class JwtManager
{
    /**
     * @param string $key
     */
    protected string $key;

    /**
     * @param string $license
     */
    protected string $license;

    /**
     * @param string $email
     */
    protected string $email;

    /**
     * @param string $identifier
     */
    protected string $identifier;

    /**
     * @param array<string> $permissions
     */
    protected array $permissions = [];

    public function setKeyFromFile(string $file_path): self
    {

        if(!file_exists($file_path)) {
            throw new KeyFileNotFound("Key file not found");
        }

        $this->setKey(file_get_contents($file_path));
        return $this;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    public function setLicense(string $license): self
    {
        $this->license = $license;
        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;
        return $this;
    }

    public function setPermissions(array $permissions): self
    {
        $this->permissions = $permissions;
        return $this;
    }

    public function generate(): string
    {

        $this->validateUser();

        return JWT::encode($this->getPayload(), $this->key, 'RS256');
    }

    protected function data(): array
    {

        return [
            'email' => $this->email,
            'identifier' => $this->identifier,
            'permissions' => $this->permissions,
        ];

    }

    protected function validateUser(): bool
    {

        $val = new Validator($this->data());
        $val->field('email')->email()->required();
        $val->field('identifier')->required();
        $val->field('permissions')->array()->required();

        if(!$val->is_valid()) {
            throw new UserInvalidException($val->displayErrors());
        }

        return true;
    }

    protected function getPayload(): array
    {
        return [
            "identifier" => $this->identifier,
            "email" => $this->email,
            "bot_license" => $this->license,
            "permissions" => $this->permissions,
        ];
    }
}
