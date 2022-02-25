<?php

namespace alcamo\uri;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\UriNormalizer as GuzzleHttpUriNormalizer;

class UriNormalizerTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (PHP_OS_FAMILY != 'Windows') {
            system(
                'ln -s '
                . dirname(__DIR__) . ' '
                . __DIR__ . '/foobar'
            );
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (PHP_OS_FAMILY != 'Windows') {
            system('rm ' . __DIR__ . '/foobar');
        }
    }

    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize(
        $uri,
        $flags,
        $expectedUri
    ) {
        $normalizedUri = UriNormalizer::normalize(new Uri($uri), $flags);

        $this->assertEquals(
            $expectedUri ?? (string)$uri,
            (string)$normalizedUri
        );
    }

    public function normalizeProvider()
    {
        $dataSets = [
            'no-realpath' => [
                'file:///foo/bar/baz/qux',
                GuzzleHttpUriNormalizer::PRESERVING_NORMALIZATIONS,
                null
            ],
            'no-scheme' => [
                '/foo/bar/baz/qux', null, '/foo/bar/baz/qux'
            ],
            'not-local' => [
                'file://foo.example.org/bar/baz/../qux',
                null,
                'file://foo.example.org/bar/qux',
            ],
            'relative' => [
                'foo/bar/baz', null, 'foo/bar/baz'
            ]
        ];

        if (PHP_OS_FAMILY != 'Windows') {
            $dataSets['realpath'] = [
                'file://' . __DIR__ . '/foobar/tests/UriNormalizerTest.php',
                null,
                'file://' . __FILE__,
            ];
        }

        return $dataSets;
    }
}
