<?php

declare(strict_types=1);

namespace MountSoftware\SymfonyToonSerializer;

use HelgeSverre\Toon\DecodeOptions;
use HelgeSverre\Toon\EncodeOptions;
use HelgeSverre\Toon\Toon;

/**
 * Standalone helper service for TOON encoding and decoding.
 *
 * This service provides a convenient wrapper around the TOON library
 * for use cases where the Symfony Serializer component is not needed.
 *
 * ## Usage Example
 *
 * ```php
 * use MountSoftware\SymfonyToonSerializer\ToonService;
 * use MountSoftware\SymfonyToonSerializer\ToonOptions;
 *
 * // Create service with default options
 * $toon = new ToonService([
 *     ToonOptions::DELIMITER => ToonOptions::DELIMITER_TAB,
 *     ToonOptions::STRICT => false,
 * ]);
 *
 * // Encode data
 * $encoded = $toon->encode(['id' => 1, 'name' => 'Alice']);
 *
 * // Decode data with custom options
 * $decoded = $toon->decode($encoded, [
 *     ToonOptions::STRICT => true,
 * ]);
 * ```
 *
 * @see https://github.com/HelgeSverre/toon
 * @see ToonOptions For available option constants
 */
final class ToonService
{
    /**
     * @param array<string, mixed> $defaultOptions Default options for encoding/decoding
     */
    public function __construct(
        private readonly array $defaultOptions = []
    ) {
    }

    /**
     * Encode data to TOON format.
     *
     * Available options:
     * - delimiter: Field delimiter (',', "\t", or '|')
     * - indent: Number of spaces for indentation (default: 2)
     * - lengthMarker: Array length marker ('#' or false)
     *
     * @param mixed $data The data to encode
     * @param array<string, mixed> $options Additional options (merged with defaults)
     * @return string The TOON-encoded string
     * @throws \InvalidArgumentException if options are invalid
     */
    public function encode(mixed $data, array $options = []): string
    {
        $mergedOptions = array_replace($this->defaultOptions, $options);

        ToonOptions::validateEncodeOptions($mergedOptions);
        $encodeOptions = $this->createEncodeOptions($mergedOptions);

        return Toon::encode($data, $encodeOptions);
    }

    /**
     * Decode TOON format to PHP data.
     *
     * Available options:
     * - strict: Enable strict mode validation (default: true)
     * - indent: Expected indentation level (default: 2)
     *
     * @param string $toon The TOON-encoded string
     * @param array<string, mixed> $options Additional options (merged with defaults)
     * @return mixed The decoded PHP data
     * @throws \InvalidArgumentException if options are invalid
     */
    public function decode(string $toon, array $options = []): mixed
    {
        $mergedOptions = array_replace($this->defaultOptions, $options);

        ToonOptions::validateDecodeOptions($mergedOptions);
        $decodeOptions = $this->createDecodeOptions($mergedOptions);

        return Toon::decode($toon, $decodeOptions);
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

