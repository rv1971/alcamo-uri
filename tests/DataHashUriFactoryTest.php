<?php

namespace alcamo\uri;

use PHPUnit\Framework\TestCase;

class DataHashUriFactoryTest extends TestCase
{
    public function testBasics(): void
    {
        $factory = new DataHashUriFactory();

        $this->assertSame(
            'tag:rv1971@web.de,2021:alcamo:hash:md5:'
                . '9e107d9d372bb6826bd81d3542a419d6',
            $factory->create('The quick brown fox jumps over the lazy dog')
        );

        $this->assertSame(
            'tag:rv1971@web.de,2021:alcamo:hash:sha1:'
                . '2fd4e1c67a2d28fced849ee1bb76e7391b93eb12',
            $factory
                ->create('The quick brown fox jumps over the lazy dog', 'sha1')
        );
    }
}
