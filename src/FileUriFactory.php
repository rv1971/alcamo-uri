<?php

namespace alcamo\uri;

use alcamo\exception\{FileNotFound, Unsupported};

/**
 * @brief Factory for file: URIs
 *
 * @warning None of the methods checks its argument for syntactical
 * correctness. For instance, on a windows system, fsPath2FileUrlPath() will
 * silently convert `foo\\\\bar` to `foo////bar`.
 *
 * With default arguments, the constructor creates a factory appropriate to
 * the current platform which applies realpath() in create() to get a
 * canonical representation.
 *
 * With other arguments, the constructor can be used to create a factory
 * appropriate for a platform different from the current one. This is useful
 * to create URIs from paths that do not reside on the local platform.
 *
 * @sa [RFC 8089](https://datatracker.ietf.org/doc/html/rfc8089)
 *
 * @date Last reviewed 2025-10-08
 */
class FileUriFactory
{
    private $directorySeparator_; ///< string
    private $applyRealpath_;      ///< bool

    /**
     * @param $directorySeparator Directory separator. Default
     * `DIRECTORY_SEPARATOR`.
     *
     * @param $applyRealpath Whether to apply realpath() in create(). Default
     * `true` iff $directorySeparator is the actual directory separator of the
     * host system.
     */
    public function __construct(
        ?string $directorySeparator = null,
        ?bool $applyRealpath = null
    ) {
        if (
            $applyRealpath
                && isset($directorySeparator)
                && $directorySeparator != DIRECTORY_SEPARATOR
        ) {
            /**
             * @throw alcamo::exception::Unsupported when attempting to create
             * a factory that should apply realpath() but with a directory
             * separator of a different platform.
             */
            throw (new Unsupported())->setMessageContext(
                [
                    'feature' =>
                        'use of realpath() with directory separator of different platform'
                ]
            );
        }

        $this->directorySeparator_ = $directorySeparator ?? DIRECTORY_SEPARATOR;
        $this->applyRealpath_ = $applyRealpath ??
            ($this->directorySeparator_ == DIRECTORY_SEPARATOR);
    }

    /// Directory separator used for filesystem paths
    public function getDirectorySeparator(): string
    {
        return $this->directorySeparator_;
    }

    /// Whether to apply realpath()
    public function getApplyRealpath(): bool
    {
        return $this->applyRealpath_;
    }

    /**
     * @brief Convert a local filesystem path to a path for use in a file: URI
     *
     * @note [Section 2.2 of RFC
     * 3986](https://datatracker.ietf.org/doc/html/rfc3986#section-2.2) states
     * that URIs that differ in the replacement of a reserved character with
     * its corresponding percent-encoded octet are not equivalent. Given that
     * the drive-letter production in [appendix E.2 of RFC
     * 8089](https://datatracker.ietf.org/doc/html/rfc8089#appendix-E.2)
     * clearly prescribes a literal colon, colons MUST NOT be percent-encoded,
     * and therefore the present method does not percent-encode them.
     */
    public function fsPath2FileUrlPath(string $path): string
    {
        return str_replace(
            '%3A',
            ':',
            implode(
                '/',
                array_map(
                    'rawurlencode',
                    explode($this->directorySeparator_, $path)
                )
            )
        );
    }

    /// Convert a path for use in a file: URI to a local filesystem path
    public function fileUrlPath2FsPath(string $path): string
    {
        return implode(
            $this->directorySeparator_,
            array_map('urldecode', explode('/', $path))
        );
    }

    /**
     * @brief Create absolute `file://` URI from local filesystem path
     *
     * @param $path Local path.
     *
     * @warning If @ref $applyRealPath_ is not set, the path is expected to be
     * an absolute path, but this is not checked.
     */
    public function create(string $path): Uri
    {
        if ($this->applyRealpath_) {
            $realpath = realpath($path);

            /* realpath() strips any trailing directory separators, hence it
             * must be appended again if there was one. */
            if ($path[-1] == $this->directorySeparator_) {
                $realpath .= $this->directorySeparator_;
            }

            if ($realpath === false) {
                /** @throw FileNotFound if realpath() fails */
                throw (new FileNotFound())->setMessageContext(
                    [ 'filename' => $path ]
                );
            }

            $uri = $this->fsPath2FileUrlPath($realpath);
        } else {
            $uri = $this->fsPath2FileUrlPath($path);
        }

        /* On systems supporting drive letters, the result of realpath()
         * does not start with a slash. */
        if ($uri[0] != '/') {
            $uri = "/$uri";
        }

        /* The GuzzleHttp implementation transforms this to a URI with three
         * slashes after the `file:`. It would transform the expression
         * "file://$uri" incorrectly to an URI with *two* slashes after the
         * `file:`. */
        return new Uri("file:$uri");
    }
}
