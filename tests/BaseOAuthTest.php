<?php

declare(strict_types=1);

namespace yiiunit\extensions\authclient;

use yii\authclient\signature\PlainText;
use yii\authclient\OAuthToken;
use yii\authclient\BaseOAuth;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\httpclient\Response;
use yiiunit\extensions\authclient\traits\OAuthDefaultReturnUrlTestTrait;

class BaseOAuthTest extends TestCase
{
    use OAuthDefaultReturnUrlTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApplication();
    }

    /**
     * Creates test OAuth client instance.
     *
     * @return BaseOAuth oauth client.
     */
    protected function createClient()
    {
        return $this->getMockBuilder(BaseOAuth::class)
            ->onlyMethods(['refreshAccessToken', 'applyAccessTokenToRequest', 'initUserAttributes'])
            ->getMock();
    }

    // Tests :

    public function testSetGet(): void
    {
        $oauthClient = $this->createClient();

        $returnUrl = 'http://test.return.url';
        $oauthClient->setReturnUrl($returnUrl);
        $this->assertEquals($returnUrl, $oauthClient->getReturnUrl(), 'Unable to setup return URL!');
    }

    public function testSetupHttpClient(): void
    {
        $oauthClient = $this->createClient();
        $oauthClient->apiBaseUrl = 'http://api.test.url';

        $this->assertEquals($oauthClient->apiBaseUrl, $oauthClient->getHttpClient()->baseUrl);

        $httpClient = new Client();
        $oauthClient->setHttpClient($httpClient);
        $actualHttpClient = $oauthClient->getHttpClient();
        $this->assertNotSame($httpClient, $actualHttpClient);
        $this->assertEquals($oauthClient->apiBaseUrl, $actualHttpClient->baseUrl);

        $oauthClient->setHttpClient([
            'transport' => 'yii\httpclient\CurlTransport',
        ]);
        $this->assertEquals($oauthClient->apiBaseUrl, $oauthClient->getHttpClient()->baseUrl);
    }

    public function testSetupComponents(): void
    {
        $oauthClient = $this->createClient();

        $oauthToken = new OAuthToken();
        $oauthClient->setAccessToken($oauthToken);
        $this->assertEquals($oauthToken, $oauthClient->getAccessToken(), 'Unable to setup token!');

        $oauthSignatureMethod = new PlainText();
        $oauthClient->setSignatureMethod($oauthSignatureMethod);
        $this->assertEquals($oauthSignatureMethod, $oauthClient->getSignatureMethod(), 'Unable to setup signature method!');
    }

    public function testSetupAccessToken(): void
    {
        $oauthClient = $this->createClient();

        $accessToken = new OAuthToken();
        $oauthClient->setAccessToken($accessToken);

        $this->assertSame($accessToken, $oauthClient->getAccessToken());

        $oauthClient->setAccessToken(['token' => 'token-mock']);
        $accessToken = $oauthClient->getAccessToken();
        $this->assertInstanceOf(OAuthToken::class, $accessToken);
        $this->assertEquals('token-mock', $accessToken->getToken());

        $oauthClient->setAccessToken(null);
        $this->assertNull($oauthClient->getAccessToken());
    }

    /**
     * @depends testSetupComponents
     * @depends testSetupAccessToken
     */
    public function testSetupComponentsByConfig(): void
    {
        $oauthClient = $this->createClient();

        $oauthToken = [
            'token' => 'test_token',
            'tokenSecret' => 'test_token_secret',
        ];
        $oauthClient->setAccessToken($oauthToken);
        $this->assertEquals($oauthToken['token'], $oauthClient->getAccessToken()->getToken(), 'Unable to setup token as config!');

        $oauthSignatureMethod = [
            'class' => 'yii\authclient\signature\PlainText',
        ];
        $oauthClient->setSignatureMethod($oauthSignatureMethod);
        $returnedSignatureMethod = $oauthClient->getSignatureMethod();
        $this->assertEquals($oauthSignatureMethod['class'], $returnedSignatureMethod::class, 'Unable to setup signature method as config!');
    }

    /**
     * @dataProvider yiiunit\extensions\authclient\Provider\Data::composeUrl
     *
     * @param string $url         request URL.
     * @param array  $params      request params
     * @param string $expectedUrl expected composed URL.
     */
    public function testComposeUrl($url, array $params, $expectedUrl): void
    {
        $oauthClient = $this->createClient();
        $composedUrl = $this->invoke($oauthClient, 'composeUrl', [$url, $params]);
        $this->assertEquals($expectedUrl, $composedUrl);
    }

    /**
     * @depends testSetupAccessToken
     *
     * @dataProvider yiiunit\extensions\authclient\Provider\Data::apiUrl
     *
     * @param $apiBaseUrl
     * @param $apiSubUrl
     * @param $expectedApiFullUrl
     */
    public function testApiUrl($apiBaseUrl, $apiSubUrl, $expectedApiFullUrl): void
    {
        $oauthClient = $this->createClient();

        $accessToken = new OAuthToken();
        $accessToken->setToken('test_access_token');
        $accessToken->setExpireDuration(1000);
        $oauthClient->setAccessToken($accessToken);

        $oauthClient->apiBaseUrl = $apiBaseUrl;

        $request = $oauthClient->createApiRequest()
            ->setUrl($apiSubUrl);

        $this->assertEquals($expectedApiFullUrl, $request->getFullUrl());
    }

    /**
     * @dataProvider yiiunit\extensions\authclient\Provider\Data::sendRequest
     *
     * @param $responseStatusCode
     * @param $expectedException
     */
    public function testSendRequest($responseStatusCode, $expectedException): void
    {
        $oauthClient = $this->createClient();

        $response = new Response();
        $response->addHeaders(['http-code' => $responseStatusCode]);
        $response->setData('success');

        $request = $this->createMock(Request::className());
        $request
            ->expects($this->any())
            ->method('send')
            ->willReturn($response);

        if ($expectedException) {
            $this->expectException($expectedException);
        }
        $result = $this->invoke($oauthClient, 'sendRequest', [$request]);
        $this->assertEquals('success', $result);
    }
}
