<?php

namespace Bookingsync\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessTokenInterface;

class BookingSyncResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response.
     *
     * @var array
     */
    protected $response;

    /**
     * The resource owner's access token.
     *
     * @var AccessTokenInterface
     */
    protected $accessToken;

    /**
     * @param array $response
     * @param AccessTokenInterface $accessToken
     */
    public function __construct(array $response, AccessTokenInterface $accessToken)
    {
        $this->response = $response;
        $this->accessToken = $accessToken;
    }

    /**
     * Returns the identifier of the authorized resource owner.
     *
     * @return int
     */
    public function getId()
    {
        return $this->response['id'];
    }

    /**
     * Returns all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }

    /**
     * Returns the email of the authorized resource owner.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->response['email'];
    }

    /**
     * Returns the business name of the authorized resource owner.
     *
     * @return string
     */
    public function getBusinessName()
    {
        return $this->response['business_name'];
    }

    /**
     * Returns the status of the authorized resource owner.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->response['status'];
    }

    /**
     * @return AccessTokenInterface
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }
}
