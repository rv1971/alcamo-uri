<?php

namespace alcamo\uri;

use PHPUnit\Framework\TestCase;

class UriFromServerEnvFactoryTest extends TestCase
{
    /**
     * @dataProvider createProvider
     */
    public function testCreateFromServerEnv($flags, $server, $expectedResult)
    {
        $factory = new UriFromServerEnvFactory($flags);

        $this->assertSame(
            $expectedResult,
            (string)$factory->create($server)
        );
    }

    public function createProvider()
    {
        return [
            [
                null,
                [
                    'REQUEST_URI' => '/',
                    'SERVER_NAME' => 'server1.example.com',
                    'SERVER_PORT' => 80
                ],
                'http://server1.example.com/'
            ],
            [
                UriFromServerEnvFactory::USE_HTTP_HOST,
                [
                    'HTTP_HOST' => 'www.example.com',
                    'SERVER_NAME' => 'server1.example.com',
                    'SERVER_PORT' => 80,
                    'REQUEST_URI' => '/foo/bar.php?baz=42'
                ],
                'http://www.example.com/foo/bar.php?baz=42'
            ],
            [
                null,
                [
                    'HTTPS' => 'on',
                    'REQUEST_URI' => '/qux.html',
                    'SERVER_NAME' => 'server7.example.com',
                    'SERVER_PORT' => 443
                ],
                'https://server7.example.com/qux.html'
            ],
            [
                null,
                [
                    'HTTPS' => 'on',
                    'REQUEST_URI' => '/bar',
                    'SERVER_NAME' => 'server7.example.com',
                    'SERVER_PORT' => 8443
                ],
                'https://server7.example.com:8443/bar'
            ]
        ];
    }
}
