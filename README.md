# Supplied classes

## Class `Uri`

Currently identical to the GuzzleHttp implementation. This might
change in the future.

## Class `UriNormalizer`

Built on top of GuzzleHttp's `UriNormalizer` with the additional
possibility to apply realpath() on local `file:` URIs.

This makes it easy to check whether two `file:` URIs refer to the same
physical local file.

## Class `FileUriFactory`

Helper class that converts back and forth between `file:` URIs and
local file paths.

## Class `FsPath2FileUrlIterator`

Creates an iterator for `file:` URIs from an iterator of local paths.

## Class `UriFromServerEnvFactory`

Creates an URI from `$_SERVER`.

## Class `UriFromCurieFactory`

Provides various methods to create URIs from CURIEs.
