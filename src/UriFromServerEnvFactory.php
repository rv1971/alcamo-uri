<?php

namespace alcamo\uri;

/**
 * @brief Create an URI from $_SERVER.
 *
 * @date Last reviewed 2025-10-08
 */
class UriFromServerEnvFactory
{
    /**
     * @brief In create(), use HTTP_HOST rather than SERVER_NAME
     *
     * `HTTP_HOST` is client-contolled so it probably reflects what the user
     * sees in the web browser, while `SERVER_NAME` is server-controlled and
     * thus is not subject to user manipulation, provided the server is
     * correctly configured.
     *
     * @sa [What is the difference between HTTP_HOST and SERVER_NAME in PHP?](https://stackoverflow.com/questions/2297403/what-is-the-difference-between-http-host-and-server-name-in-php#comment21447025_2297421)
     */
    public const USE_HTTP_HOST = 1;

    private $flags_; ///< int

    public function __construct(?int $flags = null)
    {
        $this->flags_ = (int)$flags;
    }

    /**
     * @brief Create from server environment information such as $_SERVER
     *
     * @sa https://stackoverflow.com/questions/6768793/get-the-full-url-in-php
     */
    public function create(array $server): Uri
    {
        return new Uri(
            (isset($server['HTTPS']) ? 'https://' : 'http://')
                . ($this->flags_ & self::USE_HTTP_HOST
                   ? $server['HTTP_HOST']
                   : $server['SERVER_NAME'])
                . ":{$server['SERVER_PORT']}"
                . $server['REQUEST_URI']
        );
    }
}
