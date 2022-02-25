<?php

namespace alcamo\uri;

use GuzzleHttp\Psr7\Uri as GuzzleHttpUri;

/**
 * @namespace alcamo::uri
 *
 * @brief PSR7 URI handling
 */

/**
 * @brief PSR7-compliant URI class
 *
 * Currently identical to the GuzzleHttp implementation. This might change in
 * the future.
 *
 * The `Laminas\Diactoros` implementation of Uri is not suitable because it
 * does not support `file:/` URIs.
 */
class Uri extends GuzzleHttpUri
{
}
