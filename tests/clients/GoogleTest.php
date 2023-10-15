<?php

declare(strict_types=1);

namespace yiiunit\extensions\authclient\clients;

use yii\authclient\BaseOAuth;
use yii\authclient\clients\Google;
use yii\authclient\OAuthToken;
use yii\authclient\signature\RsaSha;
use yiiunit\extensions\authclient\TestCase;

/**
 * @group google
 */
class GoogleTest extends TestCase
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

    public function testAuthenticateUserJwt()
    {
        $params = $this->getParam('google');
        if (empty($params['serviceAccount'])) {
            $this->markTestSkipped('Google service account name is not configured.');
        }

        $oauthClient = new Google();
        $token = $oauthClient->authenticateUserJwt($params['serviceAccount'], [
            'class' => RsaSha::className(),
            'algorithm' => OPENSSL_ALGO_SHA256,
            'privateCertificate' => $params['serviceAccountPrivateKey'],
        ]);
        $this->assertInstanceOf(OAuthToken::class, $token);
        $this->assertNotEmpty($token->getToken());
    }

    protected function createClient()
    {
        return new Google();
    }

    public static function defaultReturnUrl(): array
    {
        return [
            'default' => [['authclient' => 'google'], null, '/?authclient=google'],
            'remove extra parameter' => [['authclient' => 'google', 'extra' => 'userid'], null, '/?authclient=google'],
            'keep extra parameter' => [['authclient' => 'google', 'extra' => 'userid'], ['authclient', 'extra'], '/?authclient=google&extra=userid'],
        ];
    }

    /**
     * @dataProvider defaultReturnUrl
     *
     * @param $requestQueryParams
     * @param $parametersToKeepInReturnUrl
     * @param $expectedReturnUrl
     */
    public function testDefaultReturnUrl($requestQueryParams, $parametersToKeepInReturnUrl, $expectedReturnUrl)
    {
        $module = \Yii::createObject(\yii\base\Module::className(), ['module']);
        $request = \Yii::createObject([
            'class' => \yii\web\Request::className(),
            'queryParams' => $requestQueryParams,
            'scriptUrl' => '/index.php',
        ]);
        $response = \Yii::createObject([
            'class' => \yii\web\Response::className(),
            'charset' => 'UTF-8',
        ]);
        $controller = \Yii::createObject([
            'class' => \yii\web\Controller::className(),
            'request' => $request,
            'response' => $response,
        ], ['default', $module]);
        $app = $this->mockWebApplication([
            'components' => [
                'request' => $request,
                'urlManager' => [
                    'enablePrettyUrl' => true,
                    'showScriptName' => false,
                    'rules' => [
                        '/' => '/module/default',
                    ],
                ],
            ],
            'controller' => $controller,
        ]);

        /** @var BaseOAuth $oauthClient */
        $oauthClient = $this->createClient();
        if (!empty($parametersToKeepInReturnUrl)) {
            $oauthClient->parametersToKeepInReturnUrl = $parametersToKeepInReturnUrl;
        }

        $this->assertEquals($expectedReturnUrl, $oauthClient->getReturnUrl());
    }
}
