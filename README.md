# BookingSync Provider for OAuth 2.0 Client
[![Latest Version](https://img.shields.io/github/release/BookingSync/oauth2-bookingsync-php.svg?style=flat-square)](https://github.com/bookingsync/oauth2-bookingsync-php/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/BookingSync/oauth2-bookingsync-php/master.svg?style=flat-square)](https://travis-ci.org/bookingsync/oauth2-bookingsync-php)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/bookingsync/oauth2-bookingsync-php.svg?style=flat-square)](https://scrutinizer-ci.com/g/bookingsync/oauth2-bookingsync-php/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/bookingsync/oauth2-bookingsync-php.svg?style=flat-square)](https://scrutinizer-ci.com/g/bookingsync/oauth2-bookingsync-php)
[![Total Downloads](https://img.shields.io/packagist/dt/bookingsync/oauth2-bookingsync-php.svg?style=flat-square)](https://packagist.org/packages/bookingsync/oauth2-bookingsync-php)

This package provides BookingSync OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require bookingsync/oauth2-bookingsync-php
```

## Usage

Usage is the same as The League's OAuth client, using `\Bookingsync\OAuth2\Client\Provider\BookingSyncProvider` as the provider.

### Authorization Code Flow

```php
use Bookingsync\OAuth2\Client\Provider\BookingSyncProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

$provider = new BookingSyncProvider([
    'clientId'          => 'XXXXXXXX',
    'clientSecret'      => 'XXXXXXXX',
    'redirectUri'       => 'https://www.example.com/callback-url', // https is mandatory for BookingSync
    'scopes'            => ['public'] // scopes required by your BookingSync application.
]);

if (! isset($_GET['code'])) {
    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {
    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {
        // Using the access token, we may look up details about the resource owner.
        $resourceOwner = $provider->getResourceOwner($accessToken);

        // Use these details to create a new profile
        printf('Hello %s!', $resourceOwner->getBusinessName());

    } catch (IdentityProviderException $e) {
        // Failed to get user details
        exit($e->getMessage());
    }

    // Use this to interact with an API on the users behalf
    echo $token->getAccessToken();

    // Use this to get a new access token if the old one expires
    echo $token->getRefreshToken();

    // Unix timestamp of when the token will expire, and need refreshing
    echo $token->getExpires();
}
```

### Refreshing a Token

```php
use Bookingsync\OAuth2\Client\Provider\BookingSyncProvider;
use League\OAuth2\Client\Grant\RefreshToken;
use League\OAuth2\Client\Token\AccessTokenInterface;

$provider = new BookingSyncProvider([
    'clientId'          => 'XXXXXXXX',
    'clientSecret'      => 'XXXXXXXX',
    'redirectUri'       => 'https://example.com/callback-url'
]);

/** @var AccessTokenInterface $existingAccessToken */
$existingAccessToken = getAccessTokenFromYourDataStore();

if ($existingAccessToken->hasExpired()) {
    $grant = new RefreshToken();
    $token = $provider->getAccessToken($grant, ['refresh_token' => $existingAccessToken->getRefreshToken()]);
}
```
### Client Credentials
```php
use Bookingsync\OAuth2\Client\Provider\BookingSyncProvider;
use League\OAuth2\Client\Grant\ClientCredentials;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

$provider = new BookingSyncProvider([
    'clientId'          => 'XXXXXXXX',
    'clientSecret'      => 'XXXXXXXX',
    'redirectUri'       => 'https://example.com/callback-url'
]);

try {
    // Try to get an access token using the client credentials grant.
    $grant = new ClientCredentials();
    $accessToken = $provider->getAccessToken($grant);
} catch (IdentityProviderException $e) {
    // Failed to get the access token
    exit($e->getMessage());
}
```

## Testing

```
vendor/bin/phpunit
```

## License

The MIT License (MIT). Please see [License File](https://github.com/bookingsync/oauth2-bookingsync-php/blob/master/LICENSE) for more information.
