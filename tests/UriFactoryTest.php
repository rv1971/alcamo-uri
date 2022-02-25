<?php

namespace alcamo\uri;

use PHPUnit\Framework\TestCase;
use alcamo\exception\{SyntaxError, UnknownNamespacePrefix};

class UriFactoryTest extends TestCase
{
    public static $context;

    public static function setUpBeforeClass(): void
    {
        $doc = new \DOMDocument();

        $doc->load('file://' . __DIR__ . '/foo.xml');

        self::$context = $doc->documentElement;
    }

    /**
     * @dataProvider newFromUriOrSafeCurieAndMapProvider
     */
    public function testNewFromUriOrSafeCurieAndMap(
        $uriOrSafeCurie,
        $map,
        $defaultPrefixValue,
        $expectedUri
    ) {
        $uri = (new UriFactory())->createFromUriOrSafeCurieAndMap(
            $uriOrSafeCurie,
            $map,
            $defaultPrefixValue
        );

        $this->assertEquals($expectedUri, (string)$uri);
    }

    public function newFromUriOrSafeCurieAndMapProvider()
    {
        $map = [
            'foo' => 'http://foo.example.org/',
            'bar' => 'http://bar.example.org'
        ];

        return [
            'uri' => [
                'http://baz.example.com',
                $map,
                null,
                'http://baz.example.com'
            ],
            'curie' => [
                '[foo:quux]',
                $map,
                'http://baz.example.info',
                'http://foo.example.org/quux'
            ],
            'default-with-colon' => [
                '[:?baz=42#QUUX]',
                $map,
                'http://baz.example.info',
                'http://baz.example.info?baz=42#QUUX'
            ],
            'default-without-colon' => [
                '[?baz=43#CORGE]',
                $map,
                'http://baz.example.info',
                'http://baz.example.info?baz=43#CORGE'
            ]
        ];
    }

    /**
     * @dataProvider newFromUriOrSafeCurieAndContextProvider
     */
    public function testNewFromUriOrSafeCurieAndContext(
        $uriOrSafeCurie,
        $defaultPrefixValue,
        $expectedUri
    ) {
        $uri = (new UriFactory())->createFromUriOrSafeCurieAndContext(
            $uriOrSafeCurie,
            self::$context,
            $defaultPrefixValue
        );

        $this->assertEquals($expectedUri, (string)$uri);
    }

    public function newFromUriOrSafeCurieAndContextProvider()
    {
        return [
            'uri' => [
                'http://baz.example.com',
                null,
                'http://baz.example.com'
            ],
            'curie' => [
                '[qux:#quux]',
                'http://baz.example.info',
                'http://qux.example.org#quux'
            ],
            'default-with-colon' => [
                '[:?baz=42#QUUX]',
                'http://baz.example.info',
                'http://baz.example.info?baz=42#QUUX'
            ],
            'default-without-colon' => [
                '[?baz=43#CORGE]',
                'http://baz.example.info',
                'http://baz.example.info?baz=43#CORGE'
            ]
        ];
    }

    public function testNewFromSafeCurieAndMapSyntaxException1()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(
            'Syntax error in "foo]" at offset 0 ("foo]"); safe CURIE must begin with "["'
        );

        (new UriFactory())->createFromSafeCurieAndMap('foo]', []);
    }

    public function testNewFromSafeCurieAndMapSyntaxException2()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(
            'Syntax error in "[foo" at offset 3 ("o"); safe CURIE must end with "]"'
        );

        (new UriFactory())->createFromSafeCurieAndMap('[foo', []);
    }

    public function testNewFromSafeCurieAndContextSyntaxException1()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(
            'Syntax error in "foo]" at offset 0 ("foo]"); safe CURIE must begin with "["'
        );

        (new UriFactory())
            ->createFromSafeCurieAndContext('foo]', self::$context);
    }

    public function testNewFromSafeCurieAndContextSyntaxException2()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(
            'Syntax error in "[foo" at offset 3 ("o"); safe CURIE must end with "]"'
        );

        (new UriFactory())
            ->createFromSafeCurieAndContext('[foo', self::$context);
    }

    public function testNewFromCurieAndMapPrefixException()
    {
        $this->expectException(UnknownNamespacePrefix::class);
        $this->expectExceptionMessage(
            'Unknown namespace prefix "foofoo"'
        );

        (new UriFactory())->createFromCurieAndMap('foofoo:foo', []);
    }

    public function testNewFromCurieAndContextPrefixException()
    {
        $this->expectException(UnknownNamespacePrefix::class);
        $this->expectExceptionMessage(
            'Unknown namespace prefix "foofoo"'
        );

        (new UriFactory())
            ->createFromCurieAndContext('foofoo:foo', self::$context);
    }
}
