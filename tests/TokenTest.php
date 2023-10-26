<?php

declare(strict_types=1);

namespace yiiunit\extensions\authclient;

use yii\authclient\OAuthToken;

class TokenTest extends TestCase
{
    public function testCreate(): void
    {
        $config = [
            'tokenParamKey' => 'test_token_param_key',
            'tokenSecretParamKey' => 'test_token_secret_param_key',
        ];
        $oauthToken = new OAuthToken($config);
        $this->assertIsObject($oauthToken, 'Unable to create access token!');
        foreach ($config as $name => $value) {
            $this->assertEquals($value, $oauthToken->$name, 'Unable to setup attributes by constructor!');
        }
        $this->assertTrue($oauthToken->createTimestamp > 0, 'Unable to fill create timestamp!');
    }

    public function testCreateWithIncorrectConfigOrder(): void
    {
        $config = [
            'token' => 'token',
            'tokenSecret' => 'tokenSecret',
            'tokenParamKey' => 'test_token_param_key',
            'tokenSecretParamKey' => 'test_token_secret_param_key',
        ];
        $oauthToken = new OAuthToken($config);
        $this->assertIsObject($oauthToken, 'Unable to create access token!');
        foreach ($config as $name => $value) {
            $this->assertEquals($value, $oauthToken->$name, 'Unable to setup attributes by constructor!');
        }
    }

    public function testSetupParams(): void
    {
        $oauthToken = new OAuthToken();

        $params = [
            'name_1' => 'value_1',
            'name_2' => 'value_2',
        ];
        $oauthToken->setParams($params);
        $this->assertEquals($params, $oauthToken->getParams(), 'Unable to setup params!');

        $newParamName = 'new_param_name';
        $newParamValue = 'new_param_value';
        $oauthToken->setParam($newParamName, $newParamValue);
        $this->assertEquals($newParamValue, $oauthToken->getParam($newParamName), 'Unable to setup param by name!');
    }

    /**
     * @depends testSetupParams
     */
    public function testSetupParamsShortcuts(): void
    {
        $oauthToken = new OAuthToken();

        $token = 'test_token_value';
        $oauthToken->setToken($token);
        $this->assertEquals($token, $oauthToken->getToken(), 'Unable to setup token!');

        $tokenSecret = 'test_token_secret';
        $oauthToken->setTokenSecret($tokenSecret);
        $this->assertEquals($tokenSecret, $oauthToken->getTokenSecret(), 'Unable to setup token secret!');

        $tokenExpireDuration = random_int(1000, 2000);
        $oauthToken->setExpireDuration($tokenExpireDuration);
        $this->assertEquals($tokenExpireDuration, $oauthToken->getExpireDuration(), 'Unable to setup expire duration!');
    }

    /**
     * @depends testSetupParamsShortcuts
     *
     * @dataProvider yiiunit\extensions\authclient\provider\Data::autoFetchExpireDuration
     *
     * @param $expectedExpireDuration
     */
    public function testAutoFetchExpireDuration(array $params, $expectedExpireDuration): void
    {
        $oauthToken = new OAuthToken();
        $oauthToken->setParams($params);
        $this->assertEquals($expectedExpireDuration, $oauthToken->getExpireDuration());
    }

    /**
     * @depends testSetupParamsShortcuts
     */
    public function testGetIsExpired(): void
    {
        $oauthToken = new OAuthToken();
        $expireDuration = 3600;
        $oauthToken->setExpireDuration($expireDuration);

        $this->assertFalse($oauthToken->getIsExpired(), 'Not expired token check fails!');

        $oauthToken->createTimestamp = $oauthToken->createTimestamp - ($expireDuration + 1);
        $this->assertTrue($oauthToken->getIsExpired(), 'Expired token check fails!');
    }

    /**
     * @depends testGetIsExpired
     */
    public function testGetIsValid(): void
    {
        $oauthToken = new OAuthToken();
        $expireDuration = 3600;
        $oauthToken->setExpireDuration($expireDuration);

        $this->assertFalse($oauthToken->getIsValid(), 'Empty token is valid!');

        $oauthToken->setToken('test_token');
        $this->assertTrue($oauthToken->getIsValid(), 'Filled up token is invalid!');

        $oauthToken->createTimestamp = $oauthToken->createTimestamp - ($expireDuration + 1);
        $this->assertFalse($oauthToken->getIsValid(), 'Expired token is valid!');
    }
}
