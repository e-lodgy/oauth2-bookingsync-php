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
     * Returns the address1 of the authorized resource owner.
     */
    public function getAddress1(): ?string
    {
        return $this->getValueByKey($this->response, 'address1');
    }

    /**
     * Returns the address2 of the authorized resource owner.
     */
    public function getAddress2(): ?string
    {
        return $this->getValueByKey($this->response, 'address2');
    }

    /**
     * Returns the city of the authorized resource owner.
     */
    public function getCity(): ?string
    {
        return $this->getValueByKey($this->response, 'city');
    }

    /**
     * Returns the zip of the authorized resource owner.
     */
    public function getZip(): ?string
    {
        return $this->getValueByKey($this->response, 'zip');
    }

    /**
     * Returns the state of the authorized resource owner.
     */
    public function getState(): ?string
    {
        return $this->getValueByKey($this->response, 'state');
    }

    /**
     * Returns the country code of the authorized resource owner.
     */
    public function getCountryCode(): ?string
    {
        return $this->getValueByKey($this->response, 'country_code');
    }

    /**
     * Returns the default locale of the authorized resource owner.
     */
    public function getDefaultLocale(): ?string
    {
        return $this->getValueByKey($this->response, 'default_locale');
    }

    /**
     * Returns the selected locales of the authorized resource owner.
     */
    public function getSelectedLocales(): ?array
    {
        return $this->getValueByKey($this->response, 'selected_locales');
    }

    /**
     * Returns the website of the authorized resource owner.
     */
    public function getWebsite(): ?string
    {
        return $this->getValueByKey($this->response, 'website');
    }

    /**
     * Returns the created time of the authorized resource owner.
     */
    public function getCreatedAt(): ?string
    {
        return $this->getValueByKey($this->response, 'created_at');
    }

    /**
     * Returns the updated time of the authorized resource owner.
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getValueByKey($this->response, 'updated_at');
    }

    /**
     * Returns the phones of the authorized resource owner.
     */
    public function getPhones(): ?array
    {
        return $this->getValueByKey($this->response, 'phones');
    }

    /**
     * Returns the preferences of the authorized resource owner.
     */
    public function getPreferences(): ?array
    {
        return $this->getValueByKey($this->response, 'preferences');
    }

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
