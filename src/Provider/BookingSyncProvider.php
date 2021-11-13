<?php

declare(strict_types=1);

namespace Bookingsync\OAuth2\Client\Provider;

use Bookingsync\OAuth2\Client\Exception\BookingSyncIdentityProviderException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Token\ResourceOwnerAccessTokenInterface;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class BookingSyncProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'uid';

    protected string $version = 'v3';

    /**
     * Returns the base URL for authorizing a client.
     *
     * Eg. https://oauth.service.com/authorize
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://www.bookingsync.com/oauth/authorize';
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * Eg. https://oauth.service.com/token
     */
    public function getBaseAccessTokenUrl(array $params = []): string
    {
        return 'https://www.bookingsync.com/oauth/token';
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     */
    public function getResourceOwnerDetailsUrl(AccessTokenInterface $token): string
    {
        $id = $token instanceof ResourceOwnerAccessTokenInterface ? $token->getResourceOwnerId() : null;

        return 'https://www.bookingsync.com/api/' . $this->version . '/accounts/' . $id;
    }

    /**
     * Returns the default scopes used by this provider.
     *
     * This should only be the scopes that are required to request the details
     * of the resource owner, rather than all the available scopes.
     */
    protected function getDefaultScopes(): array
    {
        return ['public'];
    }

    /**
     * Checks a provider response for errors.
     *
     * @param array|string $data Parsed response data
     *
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400 && ! isset($data['errors'])) {
            $message = $response->getReasonPhrase();
        } elseif (isset($data['errors'])) {
            $message = $this->formatErrors($data['errors']);
        }

        if (isset($message)) {
            throw new BookingSyncIdentityProviderException(
                $message,
                $response->getStatusCode(),
                (string) $response->getBody()
            );
        }
    }

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     */
    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     */
    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        if (! array_key_exists('accounts', $response) || empty($response['accounts'])) {
            throw new BookingSyncIdentityProviderException('Cannot found account', 0, $response);
        }

        return new BookingSyncResourceOwner($response['accounts'][0] ?? [], $token);
    }

    private function formatErrors(array $errors): string
    {
        $messages = [];
        foreach ($errors as $error) {
            $message = [];
            foreach ($error as $key => $messageValue) {
                if (! is_string($messageValue)) {
                    $messageValue = json_encode($messageValue) ?: '[Provider] Cannot resolve errors...';
                }

                $message[] = sprintf('%s: %s', (string) $key, $messageValue);
            }

            $messages[] = implode(', ', $message);
        }

        return implode("\n", $messages);
    }
}
