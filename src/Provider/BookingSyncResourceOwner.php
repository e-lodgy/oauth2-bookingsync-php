<?php

declare(strict_types=1);

namespace Bookingsync\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class BookingSyncResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

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
        return $this->getValueByKey($this->response, 'id');
    }

    /**
     * Returns the email of the authorized resource owner.
     */
    public function getEmail(): ?string
    {
        return $this->getValueByKey($this->response, 'email');
    }

    /**
     * Returns the business name of the authorized resource owner.
     */
    public function getBusinessName(): ?string
    {
        return $this->getValueByKey($this->response, 'business_name');
    }

    /**
     * Returns the status of the authorized resource owner.
     */
    public function getStatus(): ?string
    {
        return $this->getValueByKey($this->response, 'status');
    }

    /**
     */
    {
    /**
     * Returns all the owner details available as an array.
     */
    public function toArray(): array
    {
        return $this->response;
    }

    /**
     * Return the access token of the authorized resource owner
     */
    public function getAccessToken(): AccessTokenInterface
    {
        return $this->accessToken;
    }
}
