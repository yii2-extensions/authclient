<?php

declare(strict_types=1);

namespace yiiunit\extensions\authclient\data;

use yii\authclient\BaseClient;

/**
 * Mock for the Auth client.
 */
class TestClient extends BaseClient
{
    /**
     * {@inheritdoc}
     */
    protected function initUserAttributes()
    {
        return [];
    }
}
