<?php

namespace alcamo\uri;

use GuzzleHttp\Psr7\UriNormalizer as GuzzleHttpUriNormalizer;
use Psr\Http\Message\UriInterface;

/**
 * @brief Extended URI normalizer
 */
class UriNormalizer
{
    /// Apply realpath() to local file:/// URIs
    public const APPLY_REALPATH = 0x8000;

    /**
     * @brief Extend GuzzleHttp::Psr7::UriNormalizer::normalize()
     *
     * @param $uri URI to normalize.
     *
     * @param $flags A bitmask of normalizations to apply, see class constants
     * of GuzzleHttp\Psr7\UriNormalizer plus class constants in
     * present class. Defaults to
     * GuzzleHttp\Psr7\UriNormalizer::PRESERVING_NORMALIZATIONS | @ref
     * APPLY_REALPATH.
     */
    public static function normalize(
        UriInterface $uri,
        ?int $flags = null
    ): UriInterface {
        if (!isset($flags)) {
            $flags = GuzzleHttpUriNormalizer::PRESERVING_NORMALIZATIONS
                | self::APPLY_REALPATH;
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

            return $uri->withPath(
                $fileUriFactory->fsPath2FileUrlPath(
                    realpath(
                        $fileUriFactory->fileUrlPath2FsPath($uri->getPath())
                    )
                )
            );
        }

        return $uri;
    }
}
