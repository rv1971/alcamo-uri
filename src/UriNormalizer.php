<?php

namespace alcamo\uri;

use alcamo\exception\FileNotFound;
use GuzzleHttp\Psr7\UriNormalizer as GuzzleHttpUriNormalizer;
use Psr\Http\Message\UriInterface;

/**
 * @brief Extended URI normalizer
 *
 * @date Last reviewed 2025-10-08
 */
class UriNormalizer
{
    /// @copydoc GuzzleHttp::Psr7::UriNormalizer::PRESERVING_NORMALIZATIONS
    public const PRESERVING_NORMALIZATIONS =
        GuzzleHttpUriNormalizer::PRESERVING_NORMALIZATIONS;

    /// @copydoc GuzzleHttp::Psr7::UriNormalizer::CAPITALIZE_PERCENT_ENCODING
    public const CAPITALIZE_PERCENT_ENCODING =
        GuzzleHttpUriNormalizer::CAPITALIZE_PERCENT_ENCODING;

    /// @copydoc GuzzleHttp::Psr7::UriNormalizer::DECODE_UNRESERVED_CHARACTERS
    public const DECODE_UNRESERVED_CHARACTERS =
        GuzzleHttpUriNormalizer::DECODE_UNRESERVED_CHARACTERS;

    /// @copydoc GuzzleHttp::Psr7::UriNormalizer::CONVERT_EMPTY_PATH
    public const CONVERT_EMPTY_PATH =
        GuzzleHttpUriNormalizer::CONVERT_EMPTY_PATH;

    /// @copydoc GuzzleHttp::Psr7::UriNormalizer::REMOVE_DEFAULT_HOST
    public const REMOVE_DEFAULT_HOST =
        GuzzleHttpUriNormalizer::REMOVE_DEFAULT_HOST;

    /// @copydoc GuzzleHttp::Psr7::UriNormalizer::REMOVE_DEFAULT_PORT
    public const REMOVE_DEFAULT_PORT =
        GuzzleHttpUriNormalizer::REMOVE_DEFAULT_PORT;

    /// @copydoc GuzzleHttp::Psr7::UriNormalizer::REMOVE_DOT_SEGMENTS
    public const REMOVE_DOT_SEGMENTS =
        GuzzleHttpUriNormalizer::REMOVE_DOT_SEGMENTS;

    /// @copydoc GuzzleHttp::Psr7::UriNormalizer::REMOVE_DUPLICATE_SLASHES
    public const REMOVE_DUPLICATE_SLASHES =
        GuzzleHttpUriNormalizer::REMOVE_DUPLICATE_SLASHES;

    /// @copydoc GuzzleHttp::Psr7::UriNormalizer::SORT_QUERY_PARAMETERS
    public const SORT_QUERY_PARAMETERS =
        GuzzleHttpUriNormalizer::SORT_QUERY_PARAMETERS;

    /// Apply realpath() to local file:// URIs
    public const APPLY_REALPATH = 0x8000;

    /// Normalizations applied by default in normalize()
    public const DEFAULT_NORMALIZATIONS =
        self::PRESERVING_NORMALIZATIONS | self::APPLY_REALPATH;

    /**
     * @brief Extend GuzzleHttp::Psr7::UriNormalizer::normalize()
     *
     * @param $uri URI to normalize.
     *
     * @param $flags A bitmask of normalizations to apply, see class constants
     * of GuzzleHttp\Psr7\UriNormalizer plus class constants in present class.
     */
    public static function normalize(
        UriInterface $uri,
        ?int $flags = null
    ): UriInterface {
        if (!isset($flags)) {
            $flags = static::DEFAULT_NORMALIZATIONS;
        }

        $uri = GuzzleHttpUriNormalizer::normalize($uri, $flags);

        /** @ref APPLY_REALPATH is considered only if the scheme is `file` and
         *  no host is given. */
        if (
            $flags & self::APPLY_REALPATH
            && $uri->getScheme() == 'file'
            && $uri->getHost() == ''
        ) {
            $fileUriFactory = new FileUriFactory();

            $path = $fileUriFactory->fileUriPath2FsPath($uri->getPath());

            $realpath = realpath($path);

            if ($realpath === false) {
                /** @throw FileNotFound if realpath() fails */
                throw (new FileNotFound())->setMessageContext(
                    [ 'filename' => $path ]
                );
            }

            return
                $uri->withPath($fileUriFactory->fsPath2FileUriPath($realpath));
        }

        return $uri;
    }
}
