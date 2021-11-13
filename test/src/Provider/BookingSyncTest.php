<?php

declare(strict_types=1);

namespace Bookingsync\OAuth2\Client\Test\Provider;

use Bookingsync\OAuth2\Client\Exception\BookingSyncIdentityProviderException;
use Bookingsync\OAuth2\Client\Provider\BookingSyncProvider;
use Bookingsync\OAuth2\Client\Provider\BookingSyncResourceOwner;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class BookingSyncTest extends TestCase
{
    private BookingSyncProvider $provider;

    public function testGetAuthorizationUrl(): void
    {
        $url = $this->getProvider()->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->getProvider()->getState());
    }

    public function testGetBaseAuthorizationUrl(): void
    {
        $url = $this->getProvider()->getBaseAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/oauth/authorize', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl(): void
    {
        $url = $this->getProvider()->getBaseAccessTokenUrl();
        $uri = parse_url($url);

        $this->assertEquals('/oauth/token', $uri['path']);
    }

    public function testGetResourceOwnerDetailsUrl(): void
    {
        $accessTokenBody = [
            'access_token' => 'mock_access_token',
            'expires' => 3600,
            'refresh_token' => 'mock_refresh_token',
            'uid' => 1,
        ];

        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getHeader')->times(1)->andReturn('application/json');
        $response->shouldReceive('getStatusCode')->times(1)->andReturn(200);
        $response->shouldReceive('getBody')->times(1)->andReturn(json_encode($accessTokenBody));

        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('setBaseUrl')->times(1);
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->getProvider()->setHttpClient($client);

        $token = $this->getProvider()->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $url = $this->getProvider()->getResourceOwnerDetailsUrl($token);

        $uri = parse_url($url);

        $this->assertEquals('/api/v3/accounts/1', $uri['path']);
    }

    public function testGetAccessToken(): void
    {
        $accessTokenBody = [
            'access_token' => 'mock_access_token',
            'expires' => 3600,
            'refresh_token' => 'mock_refresh_token',
            'uid' => 1,
        ];

        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getHeader')->times(1)->andReturn('application/json');
        $response->shouldReceive('getStatusCode')->times(1)->andReturn(200);
        $response->shouldReceive('getBody')->times(1)->andReturn(json_encode($accessTokenBody));

        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('setBaseUrl')->times(1);
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->getProvider()->setHttpClient($client);

        $token = $this->getProvider()->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertFalse($token->hasExpired());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
        $this->assertEquals('mock_refresh_token', $token->getRefreshToken());
        $this->assertEquals('1', $token->getResourceOwnerId());
    }

    public function testUserData(): void
    {
        $accessTokenBody = [
            'access_token' => 'mock_access_token',
            'token_type' => 'Bearer',
            'expires' => 3600,
            'refresh_token' => 'mock_refresh_token',
            'scope' => 'scope1 scope2',
        ];

        $postResponse = m::mock(ResponseInterface::class);
        $postResponse->shouldReceive('getHeader')->times(1)->andReturn('application/json');
        $postResponse->shouldReceive('getStatusCode')->times(1)->andReturn(200);
        $postResponse->shouldReceive('getBody')->times(1)->andReturn(json_encode($accessTokenBody));

        $accountBody = [
            'accounts' => [[
                'id' => 12,
                'business_name' => 'mock_business_name',
                'email' => 'mock_email',
                'status' => 'mock_status',
                'created_at' => '2020-02-11T10:50:09Z',
                'updated_at' => '2021-05-06T13:25:40Z',
                'address1' => 'mock_address1',
                'address2' => 'mock_address2',
                'city' => 'mock_city',
                'zip' => 'mock_zip',
                'state' => 'mock_state',
                'country_code' => 'mock_country_code',
                'website' => 'mock_website',
                'default_locale' => 'en',
                'selected_locales' => [
                    'en'
                ],
                'preferences' => [
                    'bookings' => [
                        'default_arrival_time' => 16,
                        'default_departure_time' => 10,
                        'default_communication_locale' => 'en',
                    ],
                ],
                'phones' => [
                    'phone' => 'mock_phone',
                    'mobile' => 'mock_mobile',
                    'fax' => 'mock_fax'
                ],
            ]],
        ];

        $getResponse = m::mock(ResponseInterface::class);
        $getResponse->shouldReceive('getHeader')->times(1)->andReturn('application/json');
        $getResponse->shouldReceive('getStatusCode')->times(1)->andReturn(200);
        $getResponse->shouldReceive('getBody')->times(4)->andReturn(json_encode($accountBody));

        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('setBaseUrl')->times(5);
        $client->shouldReceive('setDefaultOption')->times(4);
        $client->shouldReceive('send')->times(1)->andReturn($postResponse);
        $client->shouldReceive('send')->times(4)->andReturn($getResponse);
        $this->getProvider()->setHttpClient($client);

        $token = $this->getProvider()->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        /** @var BookingSyncResourceOwner $user */
        $user = $this->getProvider()->getResourceOwner($token);

        $this->assertEquals(12, $user->getId());
        $this->assertEquals('mock_business_name', $user->getBusinessName());
        $this->assertEquals('mock_email', $user->getEmail());
        $this->assertEquals('mock_status', $user->getStatus());
        $this->assertEquals('2020-02-11T10:50:09Z', $user->getCreatedAt());
        $this->assertEquals('2021-05-06T13:25:40Z', $user->getUpdatedAt());
        $this->assertEquals('mock_address1', $user->getAddress1());
        $this->assertEquals('mock_address2', $user->getAddress2());
        $this->assertEquals('mock_city', $user->getCity());
        $this->assertEquals('mock_zip', $user->getZip());
        $this->assertEquals('mock_state', $user->getState());
        $this->assertEquals('mock_country_code', $user->getCountryCode());
        $this->assertEquals('mock_website', $user->getWebsite());
        $this->assertEquals('en', $user->getDefaultLocale());
        $this->assertEquals(['en'], $user->getSelectedLocales());
        $this->assertEquals(['bookings' => ['default_arrival_time' => 16, 'default_departure_time' => 10, 'default_communication_locale' => 'en']], $user->getPreferences());
        $this->assertEquals(['phone' => 'mock_phone', 'mobile' => 'mock_mobile', 'fax' => 'mock_fax'], $user->getPhones());
        $this->assertSame($token, $user->getAccessToken());
        $this->assertIsArray($user->toArray());
    }

    public function testUserDataFails(): void
    {
        $errorBodies = [[
            'error' => 'mock_error',
            'error_description' => 'mock_error_description',
        ], [
            'error' => ['message' => 'mock_error'], 'error_description' => 'mock_error_description',
        ]];

        $testPayload = function ($payload) {
            $postResponse = m::mock(ResponseInterface::class);
            $postResponse->shouldReceive('getBody')->andReturn('{"access_token": "mock_access_token","scopes": "account","expires_in": 3600,"refresh_token": "mock_refresh_token","token_type": "bearer"}');
            $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
            $postResponse->shouldReceive('getStatusCode')->andReturn(200);

            $userResponse = m::mock(ResponseInterface::class);
            $userResponse->shouldReceive('getBody')->andReturn(json_encode($payload));
            $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
            $userResponse->shouldReceive('getStatusCode')->andReturn(500);
            $userResponse->shouldReceive('getReasonPhrase')->andReturn('Internal Server Error');

            $client = m::mock('GuzzleHttp\ClientInterface');
            $client->shouldReceive('send')
                ->times(2)
                ->andReturn($postResponse, $userResponse);
            $this->getProvider()->setHttpClient($client);

            $token = $this->getProvider()->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

            try {
                $this->getProvider()->getResourceOwner($token);

                return false;
            } catch (\Exception $e) {
                $this->assertInstanceOf(IdentityProviderException::class, $e);
            }

            return $payload;
        };

        $this->assertCount(2, array_filter(array_map($testPayload, $errorBodies)));
    }

    public function testHttpErrorWithMessage(): void
    {
        $this->expectException(BookingSyncIdentityProviderException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('code: unauthorized');

        $body = [
            'errors' => [[
                'code' => 'unauthorized',
            ]],
        ];

        $getResponse = m::mock(ResponseInterface::class);
        $getResponse->shouldReceive('getHeader')->times(1)->andReturn('application/json');
        $getResponse->shouldReceive('getStatusCode')->times(1)->andReturn(401);
        $getResponse->shouldReceive('getBody')->times(4)->andReturn(json_encode($body));
        $getResponse->shouldReceive('getReasonPhrase')->times(1)->andReturn('Unauthorized');

        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('setBaseUrl')->times(5);
        $client->shouldReceive('setDefaultOption')->times(4);
        $client->shouldReceive('send')->times(4)->andReturn($getResponse);
        $this->getProvider()->setHttpClient($client);

        $token = $this->getProvider()->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->getProvider()->getResourceOwner($token);
    }

    public function testErrorWithoutHttpError(): void
    {
        $this->expectException(BookingSyncIdentityProviderException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('code: validation_failed, field: start_at, title: is within a used period');
        $this->expectExceptionMessage('code: validation_failed, field: end_at, title: is within a used period');
        $this->expectExceptionMessage("code: validation_failed, field: status, title: can't be blank. Set either booked, tentative_expires_at or unavailable attributes");

        $body = [
            'errors' => [
                [
                    'code' => 'validation_failed',
                    'field' => 'start_at',
                    'title' => 'is within a used period',
                ],
                [
                    'code' => 'validation_failed',
                    'field' => 'end_at',
                    'title' => 'is within a used period',
                ],
                [
                    'code' => 'validation_failed',
                    'field' => 'status',
                    'title' => "can't be blank. Set either booked, tentative_expires_at or unavailable attributes",
                ],
            ],
        ];

        $getResponse = m::mock(ResponseInterface::class);
        $getResponse->shouldReceive('getHeader')->times(1)->andReturn('application/json');
        $getResponse->shouldReceive('getStatusCode')->times(1)->andReturn(422);
        $getResponse->shouldReceive('getBody')->times(4)->andReturn(json_encode($body));
        $getResponse->shouldReceive('getReasonPhrase')->times(1)->andReturn('Unprocessable entity');

        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('setBaseUrl')->times(5);
        $client->shouldReceive('setDefaultOption')->times(4);
        $client->shouldReceive('send')->times(4)->andReturn($getResponse);
        $this->getProvider()->setHttpClient($client);

        $token = $this->getProvider()->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->getProvider()->getResourceOwner($token);
    }

    public function testErrorWithStringInsteadOfJsonBody(): void
    {
        $this->expectException(BookingSyncIdentityProviderException::class);
        $this->expectExceptionCode(200);
        $this->expectExceptionMessage('random_parameter: {"random_parameter":"mock_parameter"}');

        $body = [
            'errors' => [[
                'random_parameter' => [
                    'random_parameter' => 'mock_parameter',
                ],
            ]],
        ];

        $getResponse = m::mock(ResponseInterface::class);
        $getResponse->shouldReceive('getHeader')->times(1)->andReturn('application/json');
        $getResponse->shouldReceive('getStatusCode')->times(1)->andReturn(200);
        $getResponse->shouldReceive('getBody')->times(4)->andReturn(json_encode($body));

        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('setBaseUrl')->times(5);
        $client->shouldReceive('setDefaultOption')->times(4);
        $client->shouldReceive('send')->times(4)->andReturn($getResponse);
        $this->getProvider()->setHttpClient($client);

        $token = $this->getProvider()->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->getProvider()->getResourceOwner($token);
    }

    /**
     * @dataProvider accountBodyProvider
     */
    public function testMissingAccountInBody($accountBody): void
    {
        $this->expectException(BookingSyncIdentityProviderException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Cannot found account');

        $accessTokenBody = [
            'access_token' => 'mock_access_token',
            'token_type' => 'Bearer',
            'expires' => 3600,
            'refresh_token' => 'mock_refresh_token',
            'scope' => 'scope1 scope2',
        ];

        $postResponse = m::mock(ResponseInterface::class);
        $postResponse->shouldReceive('getHeader')->times(1)->andReturn('application/json');
        $postResponse->shouldReceive('getStatusCode')->times(1)->andReturn(200);
        $postResponse->shouldReceive('getBody')->times(1)->andReturn(json_encode($accessTokenBody));

        $getResponse = m::mock(ResponseInterface::class);
        $getResponse->shouldReceive('getHeader')->times(1)->andReturn('application/json');
        $getResponse->shouldReceive('getStatusCode')->times(1)->andReturn(200);
        $getResponse->shouldReceive('getBody')->times(4)->andReturn(json_encode($accountBody));

        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('setBaseUrl')->times(5);
        $client->shouldReceive('setDefaultOption')->times(4);
        $client->shouldReceive('send')->times(1)->andReturn($postResponse);
        $client->shouldReceive('send')->times(4)->andReturn($getResponse);
        $this->getProvider()->setHttpClient($client);

        $token = $this->getProvider()->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->getProvider()->getResourceOwner($token);
    }

    private function getProvider(): BookingSyncProvider
    {
        if (isset($this->provider)) {
            return $this->provider;
        }

        return $this->provider = new BookingSyncProvider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
    }

    public function testStringBody(): void
    {
        $this->expectException(BookingSyncIdentityProviderException::class);
        $this->expectExceptionCode(200);
        $this->expectExceptionMessage('mock_string');

        $provider = $this->getProvider();
        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->times(1)->andReturn(200);
        $response->shouldReceive('getBody')->times(4)->andReturn('mock_string');

        $reflectionClass = new \ReflectionClass($provider);
        $reflectionMethod = $reflectionClass->getMethod('checkResponse');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($provider, $response, 'mock_string');
    }

    public function accountBodyProvider(): array
    {
        return [
            [[]],
            ['account' => []],
        ];
    }
}
