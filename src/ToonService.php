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
 * @see https://github.com/HelgeSverre/toon
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
     * @param mixed $data The data to encode
     * @param array<string, mixed> $options Additional options (merged with defaults)
     * @return string The TOON-encoded string
     */
    public function encode(mixed $data, array $options = []): string
    {
        $mergedOptions = array_replace($this->defaultOptions, $options);
        $encodeOptions = $this->createEncodeOptions($mergedOptions);

        return Toon::encode($data, $encodeOptions);
    }

    /**
     * Decode TOON format to PHP data.
     *
     * @param string $toon The TOON-encoded string
     * @param array<string, mixed> $options Additional options (merged with defaults)
     * @return mixed The decoded PHP data
     */
    public function decode(string $toon, array $options = []): mixed
    {
        $mergedOptions = array_replace($this->defaultOptions, $options);
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
            indent: $options['indent'] ?? 2,
            delimiter: $options['delimiter'] ?? ',',
            lengthMarker: $options['lengthMarker'] ?? false,
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
            indent: $options['indent'] ?? 2,
            strict: $options['strict'] ?? true,
        );
    }
}
