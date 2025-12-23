<?php

namespace alcamo\uri;

use alcamo\exception\{FileNotFound, Unsupported};
use PHPUnit\Framework\TestCase;

class FileUriFactoryTest extends TestCase
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

    public function testConstructorException()
    {
        $this->expectException(Unsupported::class);
        $this->expectExceptionMessage('use of realpath() with directory');

        new FileUriFactory('.', true);
    }

    /**
     * @dataProvider fsPath2FileUriPathProvider
     */
    public function testFsPath2FileUriPath(
        $directorySeparator,
        $path,
        $expectedPath
    ) {
        $fileUriFactory = new FileUriFactory($directorySeparator);

        $this->assertSame(
            $directorySeparator ?? DIRECTORY_SEPARATOR,
            $fileUriFactory->getDirectorySeparator()
        );

        $this->assertSame(
            $expectedPath,
            $fileUriFactory->fsPath2FileUriPath($path)
        );

        $this->assertSame(
            $path,
            $fileUriFactory->fileUriPath2FsPath(
                $fileUriFactory->fsPath2FileUriPath($path)
            )
        );

        $this->assertSame(
            $expectedPath,
            $fileUriFactory->fsPath2FileUriPath(
                $fileUriFactory->fileUriPath2FsPath($expectedPath)
            )
        );
    }

    public function fsPath2FileUriPathProvider()
    {
        return [
            [
                null,
                'foo' . DIRECTORY_SEPARATOR . ' bar?' . DIRECTORY_SEPARATOR . 'b!!z',
                'foo/%20bar%3F/b%21%21z'
            ]
        ];
    }

    /**
     * @dataProvider createProvider
     */
    public function testCreate(
        $directorySeparator,
        $applyRealpath,
        $path,
        $expectedUri
    ) {
        $fileUriFactory =
            new FileUriFactory($directorySeparator, $applyRealpath);

        $this->assertSame(
            $directorySeparator ?? DIRECTORY_SEPARATOR,
            $fileUriFactory->getDirectorySeparator()
        );

        $this->assertSame(
            $applyRealpath ?? true,
            $fileUriFactory->getApplyRealpath()
        );

        $this->assertEquals(
            $expectedUri,
            (string)$fileUriFactory->create($path)
        );
    }

    public function createProvider()
    {
        $dataSets = [
            [ '/', false, '/foo/b$r', 'file:///foo/b%24r' ],
            [ '/', false, '/foo/b$r/', 'file:///foo/b%24r/' ],
            [ '/', false, 'c:/f==/bar', 'file:///c:/f%3D%3D/bar' ],
            [ '\\', false, 'c:\\foo\\bar', 'file:///c:/foo/bar' ],
            [
                DIRECTORY_SEPARATOR,
                true,
                __FILE__,
                'file:///'
                . ltrim(strtr(__FILE__, DIRECTORY_SEPARATOR, '/'), '/')
            ],
            [
                DIRECTORY_SEPARATOR,
                true,
                __DIR__ . DIRECTORY_SEPARATOR,
                'file:///'
                . ltrim(strtr(__DIR__, DIRECTORY_SEPARATOR, '/'), '/')
                . DIRECTORY_SEPARATOR
            ]
        ];

        if (PHP_OS_FAMILY != 'Windows') {
            $dataSets[] = [
                null,
                null,
                __DIR__ . '/foobar/tests/FileUriFactoryTest.php',
                'file://' . __FILE__
            ];
        }

        return $dataSets;
    }

    public function testCreateException()
    {
        $fileUriFactory = new FileUriFactory(null, true);

        $this->expectException(FileNotFound::class);
        $this->expectExceptionMessage(
            'File "/foo" not found'
        );

        $fileUriFactory->create('/foo');
    }
}
