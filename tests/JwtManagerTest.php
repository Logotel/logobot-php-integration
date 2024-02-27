<?php

namespace Logotel\Logobot\Tests;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Logotel\Logobot\Exceptions\UserInvalidException;
use Logotel\Logobot\Manager;
use PHPUnit\Framework\TestCase;

class JwtManagerTest extends TestCase{

    public function test_jwt_generation_is_ok(){

        $email = 'test@email.com';
        $identifier = '12345';
        $license = 'license';
        $permissions = ['admin'];

        $jwt = Manager::jwt()
            ->setKey(file_get_contents(__DIR__ . '/fixtures/private_key.txt'))
            ->setLicense($license)
            ->setEmail($email)
            ->setIdentifier($identifier)
            ->setPermissions($permissions)
            ->generate();

        $decoded = JWT::decode($jwt, new Key(file_get_contents(__DIR__ . '/fixtures/public_key.txt'), 'RS256'));

        $this->assertEquals($decoded->email, $email);
        $this->assertEquals($decoded->identifier, $identifier);
        $this->assertEquals($decoded->bot_license, $license);
        $this->assertEquals($decoded->permissions, $permissions);

    }

    public function test_jwt_from_key_file_generation_is_ok(){

        $email = 'test@email.com';
        $identifier = '12345';
        $license = 'license';
        $permissions = ['admin'];

        $jwt = Manager::jwt()
            ->setKeyFromFile(__DIR__ . '/fixtures/private_key.txt')
            ->setLicense($license)
            ->setEmail($email)
            ->setIdentifier($identifier)
            ->setPermissions($permissions)
            ->generate();

        $decoded = JWT::decode($jwt, new Key(file_get_contents(__DIR__ . '/fixtures/public_key.txt'), 'RS256'));

        $this->assertEquals($decoded->email, $email);
        $this->assertEquals($decoded->identifier, $identifier);
        $this->assertEquals($decoded->bot_license, $license);
        $this->assertEquals($decoded->permissions, $permissions);

    }

    public function test_jwt_generation_is_failing(){

        $email = 'test@email.com';
        $identifier = '12345';
        $license = 'license';
        $permissions = ['admin'];

        $jwt = Manager::jwt()
            ->setKey(file_get_contents(__DIR__ . '/fixtures/private_key.txt'))
            ->setLicense($license)
            ->setEmail($email)
            ->setIdentifier($identifier)
            ->setPermissions($permissions)
            ->generate();

        $decoded = JWT::decode($jwt, new Key(file_get_contents(__DIR__ . '/fixtures/public_key.txt'), 'RS256'));

        $this->assertNotEquals($decoded->email, "another-email@test.com");
        $this->assertNotEquals($decoded->identifier, "abcd");
        $this->assertNotEquals($decoded->bot_license, "test");
        $this->assertNotEquals($decoded->permissions, []);

    }

    public function test_payload_validation(){

        try {
            $email = 'test';
            $identifier = '12345';
            $license = 'license';
            $permissions = ['admin'];

            Manager::jwt()
                ->setKey(file_get_contents(__DIR__ . '/fixtures/private_key.txt'))
                ->setLicense($license)
                ->setEmail($email)
                ->setIdentifier($identifier)
                ->setPermissions($permissions)
                ->generate();
        } catch (UserInvalidException $th) {
            $this->expectException(UserInvalidException::class);
            throw $th;
        }

    }
}