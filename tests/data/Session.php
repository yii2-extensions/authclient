<?php

declare(strict_types=1);

namespace yiiunit\extensions\authclient\data;

/**
 * Web session class mock.
 */
class Session extends \yii\web\Session
{
    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        // blank, override, preventing shutdown function registration
    }

    public function open(): void
    {
        // blank, override, preventing session start
    }
}
