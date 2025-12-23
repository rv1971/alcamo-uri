<?php

namespace alcamo\uri;

use PHPUnit\Framework\TestCase;

class FsPath2FileUriIteratorTest extends TestCase
{
    /**
     * @dataProvider basicsProvider
     */
    public function testBasics($directorySeparator, $data, $expectedResult)
    {
        $fileUriFactory = isset($directorySeparator)
            ? new FileUriFactory($directorySeparator, false)
            : null;

        $iterator = new FsPath2FileUriIterator(
            new \ArrayIterator($data),
            $fileUriFactory
        );

        $result = [];

        foreach ($iterator as $value) {
            $result[] = (string)$value;
        }

        $this->assertSame($expectedResult, $result);
    }

    public function basicsProvider()
    {
        return [
            [
                '/',
                [ '/home/bob jr', '/var/lib/foo?' ],
                [ 'file:///home/bob%20jr', 'file:///var/lib/foo%3F' ]
            ],
            [
                '\\',
                [ 'c:\\program files', 'd:\\data' ],
                [ 'file:///c:/program%20files', 'file:///d:/data' ]
            ]
        ];
    }
}
