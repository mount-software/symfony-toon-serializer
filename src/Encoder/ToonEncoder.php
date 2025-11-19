<?php

declare(strict_types=1);

namespace MountSoftware\SymfonyToonSerializer\Encoder;

use HelgeSverre\Toon\DecodeOptions;
use HelgeSverre\Toon\EncodeOptions;
use HelgeSverre\Toon\Toon;
use MountSoftware\SymfonyToonSerializer\ToonOptions;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

/**
 * TOON (Token-Oriented Object Notation) encoder/decoder for Symfony Serializer.
 *
 * This encoder integrates the TOON format with Symfony's serializer component,
 * delegating the actual encoding/decoding to the helgesverre/toon library.
 *
 * ## Context Options
 *
 * Options can be passed in two ways:
 *
 * 1. Namespaced (recommended):
 *    ```php
 *    $serializer->serialize($data, 'toon', [
 *        'toon_options' => [
 *            'delimiter' => "\t",
 *            'strict' => true,
 *        ],
 *    ]);
 *    ```
 *
 * 2. Direct (convenience):
 *    ```php
 *    $serializer->serialize($data, 'toon', [
 *        'delimiter' => "\t",
 *        'strict' => true,
 *    ]);
 *    ```
 *
 * Namespaced options take precedence if both are provided.
 *
 * ### Encoding Options
 *
 * - **delimiter** (string): Field delimiter for tabular data
 *   - `','` (default) - Comma delimiter
 *   - `"\t"` - Tab delimiter
 *   - `'|'` - Pipe delimiter
 *
 * - **indent** (int): Number of spaces for indentation (default: 2)
 *   - Must be >= 0
 *
 * - **lengthMarker** (string|false): Array length marker prefix (default: false)
 *   - `'#'` - Enable length markers
 *   - `false` - Disable length markers
 *
 * ### Decoding Options
 *
 * - **strict** (bool): Enable strict mode validation (default: true)
 *   - `true` - Strict validation (throws on errors)
 *   - `false` - Lenient mode (best-effort parsing)
 *
 * - **indent** (int): Expected indentation level (default: 2)
 *   - Must be >= 0
 *
 * @see https://github.com/HelgeSverre/toon
 * @see ToonOptions For option constants and validation
 */
final class ToonEncoder implements EncoderInterface, DecoderInterface
{
    public const FORMAT = 'toon';

    /**
     * @param array<string, mixed> $defaultOptions Default options for encoding/decoding
     */
    public function __construct(
        private readonly array $defaultOptions = []
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException if options are invalid
     */
    public function encode(mixed $data, string $format, array $context = []): string
    {
        $options = array_replace(
            $this->defaultOptions,
            ToonOptions::extractFromContext($context)
        );

        ToonOptions::validateEncodeOptions($options);
        $encodeOptions = $this->createEncodeOptions($options);

        return Toon::encode($data, $encodeOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding(string $format): bool
    {
        return self::FORMAT === $format;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException if options are invalid
     */
    public function decode(string $data, string $format, array $context = []): mixed
    {
        $options = array_replace(
            $this->defaultOptions,
            ToonOptions::extractFromContext($context)
        );

        ToonOptions::validateDecodeOptions($options);
        $decodeOptions = $this->createDecodeOptions($options);

        return Toon::decode($data, $decodeOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding(string $format): bool
    {
        return self::FORMAT === $format;
    }

    /**
     * Create EncodeOptions from array configuration.
     *
     * @param array<string, mixed> $options
     */
    private function createEncodeOptions(array $options): EncodeOptions
    {
        if (empty($options)) {
            return EncodeOptions::default();
        }

        return new EncodeOptions(
            indent: $options[ToonOptions::INDENT] ?? 2,
            delimiter: $options[ToonOptions::DELIMITER] ?? ',',
            lengthMarker: $options[ToonOptions::LENGTH_MARKER] ?? false,
        );
    }

    /**
     * Create DecodeOptions from array configuration.
     *
     * @param array<string, mixed> $options
     */
    private function createDecodeOptions(array $options): DecodeOptions
    {
        if (empty($options)) {
            return DecodeOptions::default();
        }

        return new DecodeOptions(
            indent: $options[ToonOptions::INDENT] ?? 2,
            strict: $options[ToonOptions::STRICT] ?? true,
        );
    }
}

