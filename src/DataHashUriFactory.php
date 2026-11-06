<?php

namespace alcamo\uri;

/**
 * @brief Factory for tag URIs representing hashes
 *
 * Useful to create a unique identifier from any kind of data, which is
 * syntactically a URI.
 *
 * @date Last reviewed 2026-11-06
 */
class DataHashUriFactory
{
    /// Prefix to append hash algorithm and hash value
    public const URI_PREFIX = 'tag:rv1971@web.de,2021:alcamo:hash:';

    public const DEFAULT_HASH_ALGORITHM = 'md5';

    public function create(
        string $data,
        ?string $hashAlgorithm = null
    ): string {
        if (!isset($hashAlgorithm)) {
            $hashAlgorithm = static::DEFAULT_HASH_ALGORITHM;
        }

        return static::URI_PREFIX . "$hashAlgorithm:"
            . hash($hashAlgorithm, $data);
    }
}
