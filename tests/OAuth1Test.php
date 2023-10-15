<?php

declare(strict_types=1);

namespace yiiunit\extensions\authclient;

use yii\authclient\OAuth1;
use yii\authclient\signature\BaseMethod;
use yii\authclient\OAuthToken;

class OAuth1Test extends TestCase
{
    protected function setUp(): void
    {
        $config = [
            'components' => [
                'request' => [
                    'hostInfo' => 'http://testdomain.com',
                    'scriptUrl' => '/index.php',
                ],
            ],
        ];
        $this->mockApplication($config, '\yii\web\Application');
    }

    /**
     * Creates test OAuth1 client instance.
     *
     * @return OAuth1 oauth client.
     */
    protected function createClient()
    {
        return $this->getMockBuilder(OAuth1::class)
            ->onlyMethods(['initUserAttributes'])
            ->getMock();
    }

    // Tests :

    public function testSignRequest(): void
    {
        $oauthClient = $this->createClient();

        $request = $oauthClient->createRequest();
        $request->setUrl('https://example.com?s=some');
        $request->setData([
            'a' => 'another',
        ]);

        /* @var $oauthSignatureMethod BaseMethod|\PHPUnit_Framework_MockObject_MockObject */
        $oauthSignatureMethod = $this->getMockBuilder(BaseMethod::className())
            ->onlyMethods(['getName', 'generateSignature'])
            ->getMock();
        $oauthSignatureMethod->expects($this->any())
            ->method('getName')
            ->willReturn('test');
        $oauthSignatureMethod->expects($this->any())
            ->method('generateSignature')
            ->willReturnArgument(0);

        $oauthClient->setSignatureMethod($oauthSignatureMethod);

        $oauthClient->signRequest($request);

        $signedParams = $request->getData();

        $this->assertNotEmpty($signedParams['oauth_signature'], 'Unable to sign request!');

        $parts = [
            'GET',
            'https://example.com',
            http_build_query([
                'a' => 'another',
                'oauth_nonce' => $signedParams['oauth_nonce'],
                'oauth_signature_method' => $signedParams['oauth_signature_method'],
                'oauth_timestamp' => $signedParams['oauth_timestamp'],
                'oauth_version' => $signedParams['oauth_version'],
                's' => 'some',
            ]),
        ];
        $parts = array_map('rawurlencode', $parts);
        $expectedSignature = implode('&', $parts);

        $this->assertEquals($expectedSignature, $signedParams['oauth_signature'], 'Invalid base signature string!');
    }

    /**
     * @depends testSignRequest
     */
    public function testAuthorizationHeaderMethods(): void
    {
        $oauthClient = $this->createClient();

        $request = $oauthClient->createRequest();
        $request->setUrl('https://yiiframework.com/');

        $request->setMethod('POST');
        $oauthClient->signRequest($request);
        $this->assertNotEmpty($request->getHeaders()->get('Authorization'));

        $request = $oauthClient->createRequest();
        $request->setUrl('https://yiiframework.com/');
        $request->setMethod('GET');
        $oauthClient->signRequest($request);
        $this->assertEmpty($request->getHeaders()->get('Authorization'));

        $oauthClient->authorizationHeaderMethods = ['GET'];
        $request = $oauthClient->createRequest();
        $request->setUrl('https://yiiframework.com/');
        $request->setMethod('GET');
        $oauthClient->signRequest($request);
        $this->assertNotEmpty($request->getHeaders()->get('Authorization'));

        $oauthClient->authorizationHeaderMethods = null;
        $request = $oauthClient->createRequest();
        $request->setUrl('https://yiiframework.com/');
        $request->setMethod('GET');
        $oauthClient->signRequest($request);
        $this->assertNotEmpty($request->getHeaders()->get('Authorization'));

        $oauthClient->authorizationHeaderMethods = [];
        $request = $oauthClient->createRequest();
        $request->setUrl('https://yiiframework.com/');
        $request->setMethod('POST');
        $oauthClient->signRequest($request);
        $this->assertEmpty($request->getHeaders()->get('Authorization'));
    }

    /**
     * @dataProvider yiiunit\extensions\authclient\Provider\Data::composeAuthorizationHeader
     *
     * @param string $realm                       authorization realm.
     * @param array  $params                      request params.
     * @param string $expectedAuthorizationHeader expected authorization header.
     */
    public function testComposeAuthorizationHeader($realm, array $params, $expectedAuthorizationHeader): void
    {
        $oauthClient = $this->createClient();
        $authorizationHeader = $this->invoke($oauthClient, 'composeAuthorizationHeader', [$params, $realm]);
        $this->assertEquals($expectedAuthorizationHeader, $authorizationHeader);
    }

    public function testBuildAuthUrl(): void
    {
        $oauthClient = $this->createClient();
        $authUrl = 'http://test.auth.url';
        $oauthClient->authUrl = $authUrl;

        $requestTokenToken = 'test_request_token';
        $requestToken = new OAuthToken();
        $requestToken->setToken($requestTokenToken);

        $builtAuthUrl = $oauthClient->buildAuthUrl($requestToken);

        $this->assertStringContainsString($authUrl, $builtAuthUrl, 'No auth URL present!');
        $this->assertStringContainsString($requestTokenToken, $builtAuthUrl, 'No token present!');
    }
}
