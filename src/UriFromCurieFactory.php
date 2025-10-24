<?php

namespace alcamo\uri;

use alcamo\exception\{SyntaxError, UnknownNamespacePrefix};

/**
 * @brief Factory for URIs from CURIEs
 *
 * @sa [CURIE Syntax](https://www.w3.org/TR/curie/)
 *
 * @date Last reviewed 2025-10-08
 */
class UriFromCurieFactory
{
    /**
     * @brief Create from CURIE and prefix map.
     *
     * @param $curie CURIE.
     *
     * @param $map array|ArrayAccess Map of CURIE prefixes to namespace names.
     *
     * @param $defaultPrefixValue Default prefix value to add to unprefixed
     * names.
     */
    public function createFromCurieAndMap(
        string $curie,
        $map,
        ?string $defaultPrefixValue = null
    ): Uri {
        $a = explode(':', $curie, 2);

        if (!isset($a[1]) || $a[0] == '') {
            return new Uri($defaultPrefixValue . $curie);
        }

        if (!isset($map[$a[0]])) {
            /** @throw alcamo::xml::exception::UnknownNamespacePrefix if the
             *  prefix is not found in the map. */
            throw (new UnknownNamespacePrefix())->setMessageContext(
                [
                    'prefix' => $a[0],
                    'inData' => $curie
                ]
            );
        }

        return new Uri($map[$a[0]] . $a[1]);
    }

    /**
     * @brief Create from safe CURIE and prefix map.
     *
     * @param $safeCurie Safe CURIE.
     *
     * @param $map array|ArrayAccess Map of CURIE prefixes to namespace names.
     *
     * @param $defaultPrefixValue Default prefix value to add to unprefixed
     * names.
     */
    public function createFromSafeCurieAndMap(
        string $safeCurie,
        $map,
        ?string $defaultPrefixValue = null
    ): Uri {
        if ($safeCurie[0] != '[') {
            /** @throw alcamo::exception::SyntaxError if a safe CURIE does not
             *  start with an opening bracket. */
            throw (new SyntaxError())->setMessageContext(
                [
                    'inData' => $safeCurie,
                    'atOffset' => 0,
                    'extraMessage' => 'safe CURIE must begin with "["'
                ]
            );
        }

        if ($safeCurie[-1] != ']') {
            /** @throw alcamo::exception::SyntaxError if a safe CURIE does not
             *  end with a closing bracket. */
            throw (new SyntaxError())->setMessageContext(
                [
                    'inData' => $safeCurie,
                    'atOffset' => strlen($safeCurie) - 1,
                    'extraMessage' => 'safe CURIE must end with "]"'
                ]
            );
        }

        return $this->createFromCurieAndMap(
            substr($safeCurie, 1, strlen($safeCurie) - 2),
            $map,
            $defaultPrefixValue
        );
    }

    /**
     * @brief Create from URI or safe CURIE and prefix map.
     *
     * @param $uriOrSafeCurie URI or safe CURIE.
     *
     * @param $map array|ArrayAccess Map of CURIE prefixes to values.
     *
     * @param $defaultPrefixValue Default prefix value to add to unprefixed
     * names.
     */
    public function createFromUriOrSafeCurieAndMap(
        string $uriOrSafeCurie,
        $map,
        ?string $defaultPrefixValue = null
    ): Uri {
        return $uriOrSafeCurie[0] == '['
            ? $this->createFromSafeCurieAndMap(
                $uriOrSafeCurie,
                $map,
                $defaultPrefixValue
            )
            : new Uri($uriOrSafeCurie);
    }

    /**
     * @brief Create from CURIE and DOM context node.
     *
     * @param $curie CURIE.
     *
     * @param $context Context node.
     *
     * @param $defaultPrefixValue Default prefix value to add to unprefixed
     * names. If not provided, the context's default namespace is used.
     */
    public function createFromCurieAndContext(
        string $curie,
        \DOMNode $context,
        ?string $defaultPrefixValue = null
    ): Uri {
        $a = explode(':', $curie, 2);

        if (!isset($a[1]) || $a[0] == '') {
            return new Uri(
                ($defaultPrefixValue ?? $context->lookupNamespaceUri(null))
                . $curie
            );
        }

        $nsName = $context->lookupNamespaceURI($a[0]);

        if (!isset($nsName)) {
            /** @throw alcamo::xml::exception::UnknownNamespacePrefix if the
             *  prefix cannot be resolved. */
            throw (new UnknownNamespacePrefix())->setMessageContext(
                [
                    'prefix' => $a[0],
                    'inData' => $curie,
                    'atUri' => $context->documentURI
                        ?? $context->ownerDocument->documentURI,
                    'atLine' => $context->getLineNo()
                ]
            );
        }

        return new Uri($nsName . $a[1]);
    }

    /**
     * @brief Create from safe CURIE and DOM context node.
     *
     * @param $safeCurie Safe CURIE.
     *
     * @param $context Context node.
     *
     * @param $defaultPrefixValue Default prefix value to add to unprefixed
     * names. If not provided, the context's default namespace is used.
     */
    public function createFromSafeCurieAndContext(
        string $safeCurie,
        \DOMNode $context,
        ?string $defaultPrefixValue = null
    ): Uri {
        if ($safeCurie[0] != '[') {
            /** @throw alcamo::exception::SyntaxError if a safe CURIE does not
             *  start with an opening bracket. */
            throw (new SyntaxError())->setMessageContext(
                [
                    'inData' => $safeCurie,
                    'atOffset' => 0,
                    'extraMessage' => 'safe CURIE must begin with "["'
                ]
            );
        }

        if ($safeCurie[-1] != ']') {
            /** @throw alcamo::exception::SyntaxError if a safe CURIE does not
             *  end with a closing bracket. */
            throw (new SyntaxError())->setMessageContext(
                [
                    'inData' => $safeCurie,
                    'atOffset' => strlen($safeCurie) - 1,
                    'extraMessage' => 'safe CURIE must end with "]"'
                ]
            );
        }

        return $this->createFromCurieAndContext(
            substr($safeCurie, 1, strlen($safeCurie) - 2),
            $context,
            $defaultPrefixValue
        );
    }

    /**
     * @brief Create from URI or safe CURIE and DOM context node.
     *
     * @param $uriOrSafeCurie URI or safe CURIE.
     *
     * @param $context Context node.
     *
     * @param $defaultPrefixValue Default prefix value to add to unprefixed
     * names. If not provided, the context's default namespace is used.
     */
    public function createFromUriOrSafeCurieAndContext(
        string $uriOrSafeCurie,
        \DOMNode $context,
        ?string $defaultPrefixValue = null
    ): Uri {
        return $uriOrSafeCurie[0] == '['
            ? $this->createFromSafeCurieAndContext(
                $uriOrSafeCurie,
                $context,
                $defaultPrefixValue
            )
            : new Uri($uriOrSafeCurie, null);
    }
}
