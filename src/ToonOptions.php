<?php

declare(strict_types=1);

namespace MountSoftware\SymfonyToonSerializer;

/**
 * Configuration options for TOON encoding and decoding.
 *
 * This class provides constants and validation for TOON serializer options.
 */
final class ToonOptions
{
    // Option keys
    public const DELIMITER = 'delimiter';
    public const INDENT = 'indent';
    public const LENGTH_MARKER = 'lengthMarker';
    public const STRICT = 'strict';

    // Delimiter values
    public const DELIMITER_COMMA = ',';
    public const DELIMITER_TAB = "\t";
    public const DELIMITER_PIPE = '|';

    /**
     * Valid delimiters.
     */
    public const VALID_DELIMITERS = [
        self::DELIMITER_COMMA,
        self::DELIMITER_TAB,
        self::DELIMITER_PIPE,
    ];

    /**
     * Default encoding options.
     */
    public const DEFAULT_ENCODE_OPTIONS = [
        self::DELIMITER => self::DELIMITER_COMMA,
        self::INDENT => 2,
        self::LENGTH_MARKER => false,
    ];

    /**
     * Default decoding options.
     */
    public const DEFAULT_DECODE_OPTIONS = [
        self::INDENT => 2,
        self::STRICT => true,
    ];

    /**
     * Validate encoding options.
     *
     * @param array<string, mixed> $options
     * @throws \InvalidArgumentException if options are invalid
     */
    public static function validateEncodeOptions(array $options): void
    {
        if (isset($options[self::DELIMITER])) {
            if (!in_array($options[self::DELIMITER], self::VALID_DELIMITERS, true)) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid delimiter "%s". Valid delimiters are: %s',
                    $options[self::DELIMITER],
                    implode(', ', array_map(fn($d) => $d === "\t" ? '\\t' : $d, self::VALID_DELIMITERS))
                ));
            }
        }

        if (isset($options[self::INDENT])) {
            if (!is_int($options[self::INDENT]) || $options[self::INDENT] < 0) {
                throw new \InvalidArgumentException(sprintf(
                    'Indent must be a non-negative integer, got: %s',
                    var_export($options[self::INDENT], true)
                ));
            }
        }

        if (isset($options[self::LENGTH_MARKER])) {
            if ($options[self::LENGTH_MARKER] !== false && $options[self::LENGTH_MARKER] !== '#') {
                throw new \InvalidArgumentException(sprintf(
                    'Length marker must be "#" or false, got: %s',
                    var_export($options[self::LENGTH_MARKER], true)
                ));
            }
        }
    }

    /**
     * Validate decoding options.
     *
     * @param array<string, mixed> $options
     * @throws \InvalidArgumentException if options are invalid
     */
    public static function validateDecodeOptions(array $options): void
    {
        if (isset($options[self::INDENT])) {
            if (!is_int($options[self::INDENT]) || $options[self::INDENT] < 0) {
                throw new \InvalidArgumentException(sprintf(
                    'Indent must be a non-negative integer, got: %s',
                    var_export($options[self::INDENT], true)
                ));
            }
        }

        if (isset($options[self::STRICT])) {
            if (!is_bool($options[self::STRICT])) {
                throw new \InvalidArgumentException(sprintf(
                    'Strict mode must be a boolean, got: %s',
                    var_export($options[self::STRICT], true)
                ));
            }
        }
    }

    /**
     * Extract TOON options from Symfony serializer context.
     *
     * Supports both namespaced options (toon_options) and direct options.
     * Namespaced options take precedence.
     *
     * @param array<string, mixed> $context Serializer context
     * @return array<string, mixed> Extracted TOON options
     */
    public static function extractFromContext(array $context): array
    {
        $options = [];

        // First, check for direct context options
        $directKeys = [self::DELIMITER, self::INDENT, self::LENGTH_MARKER, self::STRICT];
        foreach ($directKeys as $key) {
            if (isset($context[$key])) {
                $options[$key] = $context[$key];
            }
        }

        // Namespaced options override direct options
        if (isset($context['toon_options']) && is_array($context['toon_options'])) {
            $options = array_replace($options, $context['toon_options']);
        }

        return $options;
    }

    private function __construct()
    {
        // Prevent instantiation
    }
}
