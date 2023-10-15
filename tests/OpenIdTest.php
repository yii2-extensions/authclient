<?php

declare(strict_types=1);

namespace yiiunit\extensions\authclient;

use yii\authclient\OpenId;

class OpenIdTest extends TestCase
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

    // Tests :

    public function testSetGet(): void
    {
        $client = new OpenId();

        $trustRoot = 'http://trust.root';
        $client->setTrustRoot($trustRoot);
        $this->assertEquals($trustRoot, $client->getTrustRoot(), 'Unable to setup trust root!');

        $returnUrl = 'http://return.url';
        $client->setReturnUrl($returnUrl);
        $this->assertEquals($returnUrl, $client->getReturnUrl(), 'Unable to setup return URL!');
    }

    /**
     * @depends testSetGet
     */
    public function testGetDefaults(): void
    {
        $client = new OpenId();

        $this->assertNotEmpty($client->getTrustRoot(), 'Unable to get default trust root!');
        $this->assertNotEmpty($client->getReturnUrl(), 'Unable to get default return URL!');
    }

    public function testDiscover(): never
    {
        $this->markTestSkipped('OpenID is almost dead. There are no famous public servers that support it.');

        $url = 'http://openid.yandex.ru';
        $client = new OpenId();
        $info = $client->discover($url);
        $this->assertNotEmpty($info);
        $this->assertNotEmpty($info['url']);
        $this->assertNotEmpty($info['identity']);
        $this->assertEquals(2, $info['version']);
        $this->assertArrayHasKey('identifier_select', $info);
        $this->assertArrayHasKey('ax', $info);
        $this->assertArrayHasKey('sreg', $info);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/3633
     *
     * @dataProvider yiiunit\extensions\authclient\Provider\Data::compareUrl
     *
     * @param string $url1
     * @param string $url2
     * @param bool $expectedResult
     */
    public function testCompareUrl($url1, $url2, $expectedResult): void
    {
        $client = new OpenId();
        $comparisonResult = $this->invoke($client, 'compareUrl', [$url1, $url2]);
        $this->assertEquals($expectedResult, $comparisonResult);
    }
}
