<?php

declare(strict_types=1);

namespace yiiunit\extensions\authclient;

use yii\authclient\OAuth2;

class OAuth2Test extends TestCase
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
     * Creates test OAuth2 client instance.
     *
     * @return OAuth2 oauth client.
     */
    protected function createClient()
    {
        return $this->getMockBuilder(OAuth2::className())
            ->onlyMethods(['initUserAttributes'])
            ->getMock();
    }

    // Tests :

    public function testBuildAuthUrl(): void
    {
        $oauthClient = $this->createClient();
        $authUrl = 'http://test.auth.url';
        $oauthClient->authUrl = $authUrl;
        $clientId = 'test_client_id';
        $oauthClient->clientId = $clientId;
        $returnUrl = 'http://test.return.url';
        $oauthClient->setReturnUrl($returnUrl);

        $builtAuthUrl = $oauthClient->buildAuthUrl();

        $this->assertStringContainsString($authUrl, $builtAuthUrl, 'No auth URL present!');
        $this->assertStringContainsString($clientId, $builtAuthUrl, 'No client id present!');
        $this->assertStringContainsString(rawurlencode($returnUrl), $builtAuthUrl, 'No return URL present!');
    }

    public function testPkceCodeChallengeIsPresentInAuthUrl(): void
    {
        $oauthClient = $this->createClient();
        $oauthClient->enablePkce = true;

        $oauthClient->authUrl = 'http://test.auth.url';
        $oauthClient->clientId = 'test_client_id';
        $oauthClient->returnUrl = 'http://test.return.url';

        $builtAuthUrl = $oauthClient->buildAuthUrl();

        $this->assertStringContainsString('code_challenge=', $builtAuthUrl, 'No code challenge Present!');
        $this->assertStringContainsString('code_challenge_method=S256', $builtAuthUrl, 'No code challenge method Present!');
    }
}
