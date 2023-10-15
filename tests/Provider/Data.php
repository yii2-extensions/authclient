<?php

declare(strict_types=1);

namespace yiiunit\extensions\authclient\Provider;

final class Data
{
    public static function apiUrl(): array
    {
        return [
            [
                'http://api.base.url',
                'sub/url',
                'http://api.base.url/sub/url',
            ],
            [
                'http://api.base.url',
                'http://api.base.url/sub/url',
                'http://api.base.url/sub/url',
            ],
            [
                'http://api.base.url',
                'https://api.base.url/sub/url',
                'https://api.base.url/sub/url',
            ],
        ];
    }

    public static function autoFetchExpireDuration(): array
    {
        return [
            [
                ['expire_in' => 123345],
                123345,
            ],
            [
                ['expire' => 233456],
                233456,
            ],
            [
                ['expiry_in' => 34567],
                34567,
            ],
            [
                ['expiry' => 45678],
                45678,
            ],
        ];
    }

    public static function compareUrl(): array
    {
        return [
            [
                'http://domain.com/index.php?r=site%2Fauth&authclient=myclient',
                'http://domain.com/index.php?r=site%2Fauth&authclient=myclient',
                true
            ],
            [
                'http://domain.com/index.php?r=site%2Fauth&authclient=myclient',
                'http://domain.com/index.php?r=site/auth&authclient=myclient',
                true
            ],
            [
                'http://domain.com/index.php?r=site%2Fauth&authclient=myclient',
                'http://domain.com/index.php?r=site/auth&authclient=myclient2',
                false
            ],
            [
                'http://domain.com/index.php?r=site%2Fauth&authclient=myclient&custom=value',
                'http://domain.com/index.php?r=site%2Fauth&custom=value&authclient=myclient',
                true
            ],
            [
                'https://domain.com/index.php?r=site%2Fauth&authclient=myclient',
                'http://domain.com/index.php?r=site%2Fauth&authclient=myclient',
                false
            ],
        ];
    }

    public static function composeAuthorizationHeader(): array
    {
        return [
            [
                '',
                [
                    'oauth_test_name_1' => 'oauth_test_value_1',
                    'oauth_test_name_2' => 'oauth_test_value_2',
                ],
                ['Authorization' => 'OAuth oauth_test_name_1="oauth_test_value_1", oauth_test_name_2="oauth_test_value_2"']
            ],
            [
                'test_realm',
                [
                    'oauth_test_name_1' => 'oauth_test_value_1',
                    'oauth_test_name_2' => 'oauth_test_value_2',
                ],
                ['Authorization' => 'OAuth realm="test_realm", oauth_test_name_1="oauth_test_value_1", oauth_test_name_2="oauth_test_value_2"']
            ],
            [
                '',
                [
                    'oauth_test_name_1' => 'oauth_test_value_1',
                    'test_name_2' => 'test_value_2',
                ],
                ['Authorization' => 'OAuth oauth_test_name_1="oauth_test_value_1"']
            ],
        ];
    }

    public static function composeUrl(): array
    {
        return [
            [
                'http://test.url',
                [
                    'param1' => 'value1',
                    'param2' => 'value2',
                ],
                'http://test.url?param1=value1&param2=value2',
            ],
            [
                'http://test.url?with=some',
                [
                    'param1' => 'value1',
                    'param2' => 'value2',
                ],
                'http://test.url?with=some&param1=value1&param2=value2',
            ],
            [
                'http://test.url',
                [],
                'http://test.url',
            ],
        ];
    }

    public static function getName(): array
    {
        return [
            [OPENSSL_ALGO_SHA1, 'RSA-SHA1'],
            [OPENSSL_ALGO_SHA256, 'RSA-SHA256'],
            ['sha256', 'RSA-SHA256'],
        ];
    }

    public static function normalizeUserAttributes(): array
    {
        return [
            [
                [
                    'name' => 'raw/name',
                    'email' => 'raw/email',
                ],
                [
                    'raw/name' => 'name value',
                    'raw/email' => 'email value',
                ],
                [
                    'name' => 'name value',
                    'email' => 'email value',
                ],
            ],
            [
                [
                    'name' => function ($attributes) {
                            return $attributes['firstName'] . ' ' . $attributes['lastName'];
                        },
                ],
                [
                    'firstName' => 'John',
                    'lastName' => 'Smith',
                ],
                [
                    'name' => 'John Smith',
                ],
            ],
            [
                [
                    'email' => ['emails', 'prime'],
                ],
                [
                    'emails' => [
                        'prime' => 'some@email.com'
                    ],
                ],
                [
                    'email' => 'some@email.com',
                ],
            ],
            [
                [
                    'email' => ['emails', 0],
                    'secondaryEmail' => ['emails', 1],
                ],
                [
                    'emails' => [
                        'some@email.com',
                    ],
                ],
                [
                    'email' => 'some@email.com',
                ],
            ],
            [
                [
                    'name' => 'file_get_contents',
                ],
                [
                    'file_get_contents' => 'value',
                ],
                [
                    'name' => 'value',
                ],
            ],
        ];
    }

    public static function sendRequest(): array
    {
        return [
            'Informational' => [100, 'yii\\authclient\\InvalidResponseException'],
            'Successful' => [200, null],
            'Redirection' => [300, 'yii\\authclient\\InvalidResponseException'],
            'Client error' => [400, 'yii\\authclient\\ClientErrorResponseException'],
            'Server error' => [500, 'yii\\authclient\\InvalidResponseException'],
        ];

    }
}
