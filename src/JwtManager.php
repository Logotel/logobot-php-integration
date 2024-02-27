<?php

namespace Logotel\Logobot;

use Firebase\JWT\JWT;
use Logotel\Logobot\Exceptions\UserInvalidException;
use Logotel\Logobot\Validator\Validation;

class JwtManager{

    protected string $key;

    protected string $license;

    protected string $email;

    protected string $identifier;

    protected array $permissions;

    public function setKeyFromFile(string $file_path):self{
        $this->setKey(file_get_contents($file_path));
        return $this;
    }

    public function setKey(string $key):self{
        $this->key = $key;
        return $this;
    }

    public function setLicense(string $license):self{
        $this->license = $license;
        return $this;
    }

    public function setEmail(string $email):self{
        $this->email = $email;
        return $this;
    }

    public function setIdentifier(string $identifier):self{
        $this->identifier = $identifier;
        return $this;
    }

    public function setPermissions(array $permissions):self{
        $this->permissions = $permissions;
        return $this;
    }

    public function generate() : string{

        $this->validateUser();

        return JWT::encode($this->getPayload(), $this->key, 'RS256');
    }

    protected function validateUser() : bool{

        $val = new Validation();
        $val->name('email')->value($this->email ?? "")->pattern('email')->required();
        $val->name('identifier')->value($this->identifier ?? "")->customPattern('[A-Za-z0-9]+')->required();
        $val->name('permissions')->value($this->permissions ?? null)->pattern('array')->required();

        if(!$val->isSuccess()){
            throw new UserInvalidException($val->getErrors());
        }

        return true;
    }

    protected function getPayload() : array{
        return [
            "identifier" => $this->identifier,
            "email" => $this->email,
            "bot_license" => $this->license,
            "permissions" => $this->permissions,
        ];
    }
}