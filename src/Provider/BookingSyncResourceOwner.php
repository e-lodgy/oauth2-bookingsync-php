<?php

declare(strict_types=1);

namespace Bookingsync\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessTokenInterface;

class BookingSyncResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response.
     */
    protected array $response;

    /**
     * The resource owner's access token.
     */
    protected AccessTokenInterface $accessToken;

    public function __construct(array $response, AccessTokenInterface $accessToken)
    {
        $this->response = $response;
        $this->accessToken = $accessToken;
    }

    /**
     * Returns the identifier of the authorized resource owner.
     */
    public function getId(): ?int
    {
        return $this->response['id'];
    }

    /**
     * Returns all the owner details available as an array.
     */
    public function toArray(): array
    public function getEmail(): ?string
    {
        return $this->response;
    }

    /**
     * Returns the email of the authorized resource owner.
     */
    public function getBusinessName(): ?string
    {
        return $this->response['email'];
    }

    /**
     * Returns the business name of the authorized resource owner.
     */
    public function getStatus(): ?string
    {
        return $this->response['business_name'];
    }

    /**
     * Returns the status of the authorized resource owner.
     */
    {
        return $this->response['status'];
    }

    /**
     * Return the access token of the authorized resource owner
     */
    public function getAccessToken(): AccessTokenInterface
    {
        return $this->accessToken;
    }
}
