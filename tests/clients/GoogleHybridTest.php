<?php

namespace yiiunit\extensions\authclient\clients;

use yii\authclient\BaseOAuth;
use yii\authclient\clients\GoogleHybrid;
use yiiunit\extensions\authclient\TestCase;
use yiiunit\extensions\authclient\traits\OAuthDefaultReturnUrlTestTrait;

class GoogleHybridTest extends TestCase
{
    protected function createClient()
    {
        return new GoogleHybrid();
    }

    public static function defaultReturnUrl(): array
    {
        return [
            'default'                => [['authclient' => 'google-hybrid'], null, 'postmessage'],
            'remove extra parameter' => [['authclient' => 'google-hybrid', 'extra' => 'userid'], null, 'postmessage'],
            'keep extra parameter'   => [['authclient' => 'google-hybrid', 'extra' => 'userid'], ['authclient', 'extra'], 'postmessage'],
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
