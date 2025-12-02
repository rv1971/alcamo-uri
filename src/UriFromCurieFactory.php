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
     * @brief Create from namespace name and local name
     *
     * According to the specification, this would simply be the concatenation
     * of namespace name and local name. But there is an inconsistency with
     * XMLSchema because the namespace name of XMLSchema is
     * `http://www.w3.org/2001/XMLSchema` while URIs to XMLSchema types are
     * built by prepending `http://www.w3.org/2001/XMLSchema#`. Hence the
     * definiton needed for the `xsd` prefix for CURIEs purposes differs from
     * the definition of needed for the `xsd` prefix for QNames. As a
     * pragmatic and somewhat generic solution, this method inserts a `#`
     * between namespace name and local name if the former ends with an
     * alphanumeric character and the latter starts with an alphanumeric
     * character.
     *
     * @note Hence `http://example.org` and `123` will be result in
     * `http://example.org#123` rather than `http://example.org123`. However,
     * it seems highly unlikely that the latter is the desired result.
     *
     * @sa [CURIE Syntax 1.0](https://www.w3.org/TR/curie/)
     * @sa [XML Schema Built-in Datatypes](https://www.w3.org/TR/xmlschema11-2/#built-in-datatypes)
     */
    public function createFromNsNameAndLocalName(
        ?string $nsName,
        string $localName
    ): Uri {
        return isset($nsName)
            ? new Uri(
                ctype_alnum($nsName[-1]) && ctype_alnum($localName[0])
                    ? "$nsName#$localName"
                    : "$nsName$localName"
            )
            : new Uri($localName);
    }

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
            return $this->createFromNsNameAndLocalName(
                $defaultPrefixValue,
                $curie
            );
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

        return $this->createFromNsNameAndLocalName($map[$a[0]], $a[1]);
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
            return $this->createFromNsNameAndLocalName(
                ($defaultPrefixValue ?? $context->lookupNamespaceUri(null)),
                $curie
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

        return $this->createFromNsNameAndLocalName($nsName, $a[1]);
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
            : new Uri($uriOrSafeCurie);
    }
}
