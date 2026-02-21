<?php

/**
 * Polyfill for Dom\* classes on PHP < 8.3
 *
 * This polyfill provides the Dom\* classes which are only available in PHP 8.3+.
 * For earlier versions, it creates new classes in the Dom namespace that wrap
 * the legacy DOMDocument, DOMElement, and DOMNode classes.
 */

namespace Dom;

if (PHP_VERSION_ID < 80300) {
    /**
     * Node - wraps DOMNode
     */
    if (! class_exists('Dom\\Node')) {
        class Node
        {
            // Marker class for type checking compatibility
        }
    }

    /**
     * Element - wraps DOMElement
     */
    if (! class_exists('Dom\\Element')) {
        class Element
        {
            // Marker class for type checking compatibility
        }
    }

    /**
     * Document - wraps DOMDocument
     */
    if (! class_exists('Dom\\Document')) {
        class Document extends \DOMDocument
        {
            public function __construct(string $version = '1.0', string $encoding = '')
            {
                parent::__construct($version, $encoding);
                $this->preserveWhiteSpace = false;
            }
        }
    }

    /**
     * HTMLDocument - the main class needed by Symfony HTML Sanitizer
     */
    if (! class_exists('Dom\\HTMLDocument')) {
        class HTMLDocument extends \DOMDocument
        {
            public function __construct(string $version = '1.0', string $encoding = '')
            {
                parent::__construct($version, $encoding);
                $this->preserveWhiteSpace = false;
                $this->formatOutput = true;
            }

            /**
             * Create an HTMLDocument from a string.
             * This mimics the PHP 8.3+ Dom\HTMLDocument::createFromString() method.
             */
            public static function createFromString(string $html): self
            {
                $doc = new self;

                // Use libxml to load HTML
                libxml_use_internal_errors(true);
                if ($html === '') {
                    $doc->loadHTML('');
                } else {
                    $doc->loadHTML('<?xml encoding="UTF-8">'.$html);
                }
                libxml_clear_errors();

                return $doc;
            }
        }
    }
}
