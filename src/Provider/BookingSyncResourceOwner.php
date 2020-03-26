<?php

namespace Bookingsync\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class BookingSyncResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected $response;

    /**
     * Set response
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
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
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }

    /**
     * Return the email of the authorized resource owner.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->response['email'];
    }

    /**
     * Return the business name of the authorized resource owner.
     *
     * @return string
     */
    public function getBusinessName()
    {
        return $this->response['business_name'];
    }

    /**
     * Return the status of the authorized resource owner.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->response['status'];
    }
}