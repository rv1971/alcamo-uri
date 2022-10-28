<?php

namespace alcamo\uri;

use alcamo\exception\FileNotFound;
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

    /**
     * @dataProvider fsPath2FileUrlPathProvider
     */
    public function testFsPath2FileUrlPath(
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
            $fileUriFactory->fsPath2FileUrlPath($path)
        );

        $this->assertSame(
            $path,
            $fileUriFactory->fileUrlPath2FsPath(
                $fileUriFactory->fsPath2FileUrlPath($path)
            )
        );

        $this->assertSame(
            $expectedPath,
            $fileUriFactory->fsPath2FileUrlPath(
                $fileUriFactory->fileUrlPath2FsPath($expectedPath)
            )
        );
    }

    public function fsPath2FileUrlPathProvider()
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
            [ '/', false, 'c:/f==/bar', 'file:///c:/f%3D%3D/bar' ],
            [ '\\', false, 'c:\\foo\\bar', 'file:///c:/foo/bar' ],
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
