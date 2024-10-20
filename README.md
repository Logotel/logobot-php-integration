<p align="center">
    <img src=".art/logotel-logo.png" width="200" alt="Logotel Logo">    
</p>

![Tests](https://github.com/Logotel/logobot-php-integration/actions/workflows/test.yml/badge.svg?branch=main)
![Packagist](https://img.shields.io/packagist/v/logotel/logobot-php-integration.svg)

# Logobot PHP integration
 
This package aim to provide the integration with Logobot
 
## Installation
 
Install via composer:
 
```bash
composer require logotel/logobot-php-integration
```
 
## Generating JWT 

Generate your JWT with:

```php
use Logotel\Logobot\Manager;

$jwt = Manager::jwt()
        ->setKey(file_get_contents('/path/to/private_key.pem'))
        ->setLicense($license)
        ->setEmail($email)
        ->setIdentifier($identifier)
        ->setPermissions($permissions)
        ->setIsSuperUser($is_super_user)
        ->setExpiration($expiration)
        ->generate();
```

The key can be retrieved by a file path:

```php
use Logotel\Logobot\Manager;

$jwt = Manager::jwt()
        ->setKeyFromFile('/path/to/private_key.pem')
        ->setLicense($license)
        ->setEmail($email)
        ->setIdentifier($identifier)
        ->setPermissions($permissions)
        ->setIsSuperUser($is_super_user)
        ->setExpiration($expiration)
        ->generate();
```

### Super user

The property (bool) can set if the user has high privilege. The privileges will be managed by the server application.

### Token expiration

The token default lifetime is 24 hours. You can edit it with ` ->setExpiration(int $expiration)`. The expiration time is in seconds (deafult 60 * 60 * 24)

### User payload

| Parameter      | Type          | Description                              |
|----------------|---------------|------------------------------------------|
| email          | String        | The email address of the user            |
| identifier     | String        | The user identifier                      |
| license        | String        | The bot license                          |
| permissions    | Array(String) | The user's permissions                   |
| is_super_user  | Bool          | If the user is super user                |

## Authenticate without JWT

In case of JWT too big (>2kb) you have to use `/authenticate` endpoint.

Before proceeding create a JWT as described [here](#generating-jwt)

```php
use Logotel\Logobot\Manager;

$key = Manager::authenticate()
        ->setJwt($jwt)
        ->generate();
```

The key will be then used instead of jwt


## Uploading text (with link)


```php
use Logotel\Logobot\Manager;

Manager::textUpload()
        ->setApiKey($api_key)
        ->setIdentifier($identifier)
        ->setTitle($title)
        ->setContent($content)
        ->setLink($link)
        ->setPermissions($permissions)
        ->setLanguage($language)
        ->setMetadata($metadata)
        ->setDocumentDate($document_date)
        ->setIsSearchable(true | false)
        ->setIsGenerative(true | false)
        ->upload();
```

If you want to change the endpoint base url you can change it by:

```php
Manager::textUpload()->setApiUrl("https://something.test");
```

You can also set a custom http client

```php
Manager::textUpload()->setClient(new \GuzzleHttp\Client(...));
```

`is_searchable` and `is_generative` are `true` by default

## Bulk importer

The bulk importer functionality will take care of processing multiple file at once. The file uploaded must by a zip file, containing:
- <i>n</i> files in pdf or txt format
- a json file named `details.json` with this structure, with one entry for each file:
```json
[
  {
    "name": "name_of_the_file.pdf",
    "permissions": [
      "a",
      "list",
      "of",
      "permissions",
    ],
    "language": "selected_language",
    "metadata": {
        "some": "value",
        "another": "value",
    },
    "document_date": "2024-05-29",
    "is_searchable": true,
    "is_generative": true
  },
  {
    ...
  },
  ...
]
```

The number of the entries in the array must be `total files in zip - 1` (the json file).

#### Usage

```php
use Logotel\Logobot\Manager;

Manager::bulkImporter()
        ->setApiKey($api_key)
        ->setFilePath($file_path)
        ->upload();
```

If you want to change the endpoint base url you can change it by:

```php
Manager::textUpload()->setApiUrl("https://something.test");
```

You can also set a custom http client

```php
Manager::textUpload()->setClient(new \GuzzleHttp\Client(...));
```

## Delete document

```php
use Logotel\Logobot\Manager;

Manager::deleteDocument()
        ->setApiKey($api_key)
        ->setIdentifier($identifier)
        ->delete();
```

If you want to change the endpoint base url you can change it by:

```php
Manager::deleteDocument()->setApiUrl("https://something.test");
```

You can also set a custom http client

```php
Manager::deleteDocument()->setClient(new \GuzzleHttp\Client(...));
```

## Search engine

Search the most relevant documents based on search query.

`$jwt` is the jwt token generated using [Generating JWT](#generating-jwt)

`$query` is the search query

`$limit` is the **maximum** amount of documents to retrieve

`$filters` is an array of available filters. At the moment are supported:

| Filter key     | Type          | Description                              | Example    |
|----------------|---------------|------------------------------------------|------------|
| date_from      | Date          | Filter documents based on creation date  | 2024-06-24 |

```php
use Logotel\Logobot\Manager;

Manager::searchEngine()
        ->setApiKey($api_key)
        ->setJwt($jwt)
        ->setQuery($query)
        ->setLimit($limit)
        ->setFilters($filters)
        ->search();
```

The ouput will be

```json
[
  {
     "uuid": "fdb07973-4955-40db-a8d1-d5b8547b4c9b",
     "name": "my document",
     "icon": "file-pdf",
     "distance": 0.253548767,
     "created_at": "10/10/2024 10:10:10"
   },
   ...
]
```

`icon` possible values are:
- file-pdf 
- file-word
- file-powerpoint
- file-excel
- file-alt
- file

If you want to change the endpoint base url you can change it by:

```php
Manager::deleteDocument()->setApiUrl("https://something.test");
```

You can also set a custom http client

```php
Manager::deleteDocument()->setClient(new \GuzzleHttp\Client(...));
```

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
