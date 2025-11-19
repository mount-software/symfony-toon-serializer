<?php

declare(strict_types=1);

namespace MountSoftware\SymfonyToonSerializer\Encoder;

use HelgeSverre\Toon\DecodeOptions;
use HelgeSverre\Toon\EncodeOptions;
use HelgeSverre\Toon\Toon;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

/**
 * TOON (Token-Oriented Object Notation) encoder/decoder for Symfony Serializer.
 *
 * This encoder integrates the TOON format with Symfony's serializer component,
 * delegating the actual encoding/decoding to the helgesverre/toon library.
 *
 * @see https://github.com/HelgeSverre/toon
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
     */
    public function encode(mixed $data, string $format, array $context = []): string
    {
        $options = array_replace($this->defaultOptions, $context['toon_options'] ?? []);
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
     */
    public function decode(string $data, string $format, array $context = []): mixed
    {
        $options = array_replace($this->defaultOptions, $context['toon_options'] ?? []);
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
