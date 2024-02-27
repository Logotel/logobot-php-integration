<p align="center">
    <img src=".art/logotel-logo.png" width="200" alt="Logotel Logo">    
</p>

![Tests](https://github.com/Logotel/logobot-php-integration/actions/workflows/test.yml/badge.svg?branch=main)


# Logobot PHP integration
 
This package aim to provide the integration with Logobot
 
## Installation
 
Install via composer:
 
```bash
composer require logotel/logobot-php-integration
```
 
## Usage
 
Generate your JWT with:

```php
$jwt = Manager::jwt()
        ->setKey(file_get_contents('/path/to/private_key.pem'))
        ->setLicense($license)
        ->setEmail($email)
        ->setIdentifier($identifier)
        ->setPermissions($permissions)
        ->generate();
```

The key can be retrieved by a file path:

```php
$jwt = Manager::jwt()
        ->setKeyFromFile('/path/to/private_key.pem')
        ->setLicense($license)
        ->setEmail($email)
        ->setIdentifier($identifier)
        ->setPermissions($permissions)
        ->generate();
```

### User payload

| Parameter  | Type          | Description                          |
|------------|---------------|--------------------------------------|
| email      | String        | The email address of the user        |
| identifier | String        | The user identifier                  |
| license    | String        | The bot license                      |
| permissions| Array(String) | The user's permissions               |


 
## Contributing
 
1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request :D
 
## History
 
Version 1.00 (2024-02-27) - first commit

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email giagara@yahoo.it instead of using the issue tracker.

## Credits

-   [Garavaglia Giacomo](https://github.com/giagara)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
