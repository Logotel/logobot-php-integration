<?php

namespace Logotel\Logobot\Tests;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Logotel\Logobot\Exceptions\KeyFileNotFound;
use Logotel\Logobot\Exceptions\UserInvalidException;
use Logotel\Logobot\Manager;
use PHPUnit\Framework\TestCase;

class JwtManagerTest extends TestCase
{
    public function test_jwt_generation_is_ok()
    {

        $email = 'test@email.com';
        $identifier = '12345';
        $license = 'license';
        $permissions = ['admin'];
        $is_super_user = false;

        $jwt = Manager::jwt()
            ->setKey(file_get_contents(__DIR__ . '/fixtures/private_key.txt'))
            ->setLicense($license)
            ->setEmail($email)
            ->setIdentifier($identifier)
            ->setPermissions($permissions)
            ->setIsSuperUser($is_super_user)
            ->generate();

        $decoded = JWT::decode($jwt, new Key(file_get_contents(__DIR__ . '/fixtures/public_key.txt'), 'RS256'));

        $this->assertEquals($decoded->email, $email);
        $this->assertEquals($decoded->identifier, $identifier);
        $this->assertEquals($decoded->bot_license, $license);
        $this->assertEquals($decoded->permissions, $permissions);
        $this->assertEquals($decoded->is_super_user, $is_super_user);

    }

    public function test_jwt_generation_expired()
    {

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
            ->setExpiration(-10)
            ->generate();

        $this->expectException(ExpiredException::class);

        JWT::decode($jwt, new Key(file_get_contents(__DIR__ . '/fixtures/public_key.txt'), 'RS256'));

    }

    public function test_jwt_from_key_file_generation_is_ok()
    {

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
        $this->assertEquals($decoded->is_super_user, false);

    }

    public function test_jwt_from_wrong_key_file_generation_is_ok()
    {

        $this->expectException(KeyFileNotFound::class);

        Manager::jwt()
            ->setKeyFromFile(__DIR__ . '/fixtures/not_existing_key.txt')
            ->setLicense('license')
            ->setEmail('email@email.com')
            ->setIdentifier('1234')
            ->setPermissions(['admin'])
            ->generate();
    }

    public function test_jwt_generation_is_failing()
    {

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
        $this->assertNotEquals($decoded->is_super_user, true);

    }

    public function test_payload_validation()
    {

        try {
            $jwt = Manager::jwt()
                ->setKeyFromFile(__DIR__ . '/fixtures/private_key.txt')
                ->setLicense('license')
                ->setEmail('email@email.com')
                ->setIdentifier('1234')
                ->setPermissions(['admin'])
                ->generate();

            $this->assertNotNull($jwt);
            $this->assertNotEmpty($jwt);
        } catch (UserInvalidException $th) {
            $this->expectException(UserInvalidException::class);

            throw $th;
        }

    }

    public function test_jwt_contains_jti_and_iat()
    {
        $before = time();

        $jwt = Manager::jwt()
            ->setKey(file_get_contents(__DIR__ . '/fixtures/private_key.txt'))
            ->setLicense('license')
            ->setEmail('test@email.com')
            ->setIdentifier('12345')
            ->setPermissions(['admin'])
            ->generate();

        $decoded = JWT::decode($jwt, new Key(file_get_contents(__DIR__ . '/fixtures/public_key.txt'), 'RS256'));

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $decoded->jti
        );
        $this->assertGreaterThanOrEqual($before, $decoded->iat);
        $this->assertLessThanOrEqual(time(), $decoded->iat);
    }

    public function test_jwt_default_expiration_is_one_hour()
    {
        $jwt = Manager::jwt()
            ->setKey(file_get_contents(__DIR__ . '/fixtures/private_key.txt'))
            ->setLicense('license')
            ->setEmail('test@email.com')
            ->setIdentifier('12345')
            ->setPermissions(['admin'])
            ->generate();

        $decoded = JWT::decode($jwt, new Key(file_get_contents(__DIR__ . '/fixtures/public_key.txt'), 'RS256'));

        $this->assertEquals(60 * 60, $decoded->exp - $decoded->iat);
    }

    public function test_each_jwt_has_a_different_jti()
    {
        $manager = Manager::jwt()
            ->setKey(file_get_contents(__DIR__ . '/fixtures/private_key.txt'))
            ->setLicense('license')
            ->setEmail('test@email.com')
            ->setIdentifier('12345')
            ->setPermissions(['admin']);

        $publicKey = new Key(file_get_contents(__DIR__ . '/fixtures/public_key.txt'), 'RS256');

        $first = JWT::decode($manager->generate(), $publicKey);
        $second = JWT::decode($manager->generate(), $publicKey);

        $this->assertNotEquals($first->jti, $second->jti);
    }
}
