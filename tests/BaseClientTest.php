<?php

declare(strict_types=1);

namespace yiiunit\extensions\authclient;

use yii\authclient\BaseClient;
use yii\authclient\SessionStateStorage;

class BaseClientTest extends TestCase
{
    protected function setUp(): void
    {
        $this->mockApplication();
    }

    /**
     * Creates test OAuth client instance.
     *
     * @return BaseClient oauth client.
     */
    protected function createClient()
    {
        return $this->getMockBuilder(BaseClient::className())
            ->onlyMethods(['initUserAttributes'])
            ->getMock();
    }

    // Tests :

    public function testSetGet(): void
    {
        $client = $this->createClient();

        $id = 'test_id';
        $client->setId($id);
        $this->assertEquals($id, $client->getId(), 'Unable to setup id!');

        $name = 'test_name';
        $client->setName($name);
        $this->assertEquals($name, $client->getName(), 'Unable to setup name!');

        $title = 'test_title';
        $client->setTitle($title);
        $this->assertEquals($title, $client->getTitle(), 'Unable to setup title!');

        $userAttributes = [
            'attribute1' => 'value1',
            'attribute2' => 'value2',
        ];
        $client->setUserAttributes($userAttributes);
        $this->assertEquals($userAttributes, $client->getUserAttributes(), 'Unable to setup user attributes!');

        $normalizeUserAttributeMap = [
            'name' => 'some/name',
            'email' => 'some/email',
        ];
        $client->setNormalizeUserAttributeMap($normalizeUserAttributeMap);
        $this->assertEquals($normalizeUserAttributeMap, $client->getNormalizeUserAttributeMap(), 'Unable to setup normalize user attribute map!');

        $viewOptions = [
            'option1' => 'value1',
            'option2' => 'value2',
        ];
        $client->setViewOptions($viewOptions);
        $this->assertEquals($viewOptions, $client->getViewOptions(), 'Unable to setup view options!');

        $requestOptions = [
            'option1' => 'value1',
            'option2' => 'value2',
        ];
        $client->setRequestOptions($requestOptions);
        $this->assertEquals($requestOptions, $client->getRequestOptions(), 'Unable to setup request options!');
    }

    public function testGetDefaults(): void
    {
        $client = $this->createClient();

        $this->assertNotEmpty($client->getName(), 'Unable to get default name!');
        $this->assertNotEmpty($client->getTitle(), 'Unable to get default title!');
        $this->assertNotNull($client->getViewOptions(), 'Unable to get default view options!');
        $this->assertNotNull($client->getNormalizeUserAttributeMap(), 'Unable to get default normalize user attribute map!');
    }

    /**
     * @dataProvider yiiunit\extensions\authclient\Provider\Data::normalizeUserAttributes
     *
     * @depends testSetGet
     *
     * @param array $normalizeUserAttributeMap
     * @param array $rawUserAttributes
     * @param array $expectedNormalizedUserAttributes
     */
    public function testNormalizeUserAttributes($normalizeUserAttributeMap, $rawUserAttributes, $expectedNormalizedUserAttributes): void
    {
        $client = $this->createClient();
        $client->setNormalizeUserAttributeMap($normalizeUserAttributeMap);

        $client->setUserAttributes($rawUserAttributes);
        $normalizedUserAttributes = $client->getUserAttributes();

        $this->assertEquals(array_merge($rawUserAttributes, $expectedNormalizedUserAttributes), $normalizedUserAttributes);
    }

    public function testSetupHttpClient(): void
    {
        $client = $this->createClient();

        $client->setHttpClient([
            'baseUrl' => 'http://domain.com',
        ]);
        $httpClient = $client->getHttpClient();

        $this->assertInstanceOf(\yii\httpclient\Client::class, $httpClient, 'Unable to setup http client.');
        $this->assertEquals('http://domain.com', $httpClient->baseUrl, 'Unable to setup http client property.');

        $client = $this->createClient();
        $httpClient = $client->getHttpClient();
        $this->assertInstanceOf(\yii\httpclient\Client::class, $httpClient, 'Unable to get default http client.');
    }

    /**
     * @depends testSetGet
     * @depends testSetupHttpClient
     */
    public function testCreateRequest(): void
    {
        $client = $this->createClient();

        $request = $client->createRequest();
        $this->assertInstanceOf(\yii\httpclient\Request::class, $request);

        $options = [
            'userAgent' => 'Test User Agent',
        ];
        $client->setRequestOptions($options);
        $request = $client->createRequest();
        $expectedOptions = array_merge($options, $this->invoke($client, 'defaultRequestOptions'));
        $this->assertEquals($expectedOptions, $request->getOptions());
    }

    public function testSetupStateStorage(): void
    {
        $client = $this->createClient();

        $stateStorage = new SessionStateStorage();
        $client->setStateStorage($stateStorage);

        $this->assertSame($stateStorage, $client->getStateStorage(), 'Unable to setup state storage.');

        $client = $this->createClient();
        $stateStorage = $client->getStateStorage();
        $this->assertInstanceOf(SessionStateStorage::class, $stateStorage, 'Unable to get default http client.');
    }
}
