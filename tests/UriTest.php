<?php

namespace alcamo\uri;

use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    /**
     * @dataProvider isAbsoluteProvider
     */
    public function testIsAbsolute($uri, $expectedResult): void
    {
        $this->assertSame($expectedResult, Uri::isAbsolute(new Uri($uri)));
    }

    public function isAbsoluteProvider(): array
    {
        return [
            [ 'http://www.example.com/test', true ],
            [ 'foo/bar', false ],
            [ 'data:,A%20brief%20note', true ]
        ];
    }
}
