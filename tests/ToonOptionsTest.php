<?php

declare(strict_types=1);

namespace MountSoftware\SymfonyToonSerializer\Tests;

use MountSoftware\SymfonyToonSerializer\ToonOptions;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for ToonOptions validation and context extraction.
 */
class ToonOptionsTest extends TestCase
{
    // ===== Validation Tests =====

    public function testValidateEncodeOptionsAcceptsValidDelimiters(): void
    {
        foreach (ToonOptions::VALID_DELIMITERS as $delimiter) {
            ToonOptions::validateEncodeOptions([
                ToonOptions::DELIMITER => $delimiter,
            ]);

            $this->addToAssertionCount(1);
        }
    }

    public function testValidateEncodeOptionsRejectsInvalidDelimiter(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid delimiter');

        ToonOptions::validateEncodeOptions([
            ToonOptions::DELIMITER => ';',
        ]);
    }

    public function testValidateEncodeOptionsAcceptsValidIndent(): void
    {
        ToonOptions::validateEncodeOptions([
            ToonOptions::INDENT => 0,
        ]);

        ToonOptions::validateEncodeOptions([
            ToonOptions::INDENT => 4,
        ]);

        $this->addToAssertionCount(2);
    }

    public function testValidateEncodeOptionsRejectsNegativeIndent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Indent must be a non-negative integer');

        ToonOptions::validateEncodeOptions([
            ToonOptions::INDENT => -1,
        ]);
    }

    public function testValidateEncodeOptionsRejectsNonIntegerIndent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Indent must be a non-negative integer');

        ToonOptions::validateEncodeOptions([
            ToonOptions::INDENT => '2',
        ]);
    }

    public function testValidateEncodeOptionsAcceptsValidLengthMarker(): void
    {
        ToonOptions::validateEncodeOptions([
            ToonOptions::LENGTH_MARKER => '#',
        ]);

        ToonOptions::validateEncodeOptions([
            ToonOptions::LENGTH_MARKER => false,
        ]);

        $this->addToAssertionCount(2);
    }

    public function testValidateEncodeOptionsRejectsInvalidLengthMarker(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Length marker must be "#" or false');

        ToonOptions::validateEncodeOptions([
            ToonOptions::LENGTH_MARKER => '*',
        ]);
    }

    public function testValidateDecodeOptionsAcceptsValidStrict(): void
    {
        ToonOptions::validateDecodeOptions([
            ToonOptions::STRICT => true,
        ]);

        ToonOptions::validateDecodeOptions([
            ToonOptions::STRICT => false,
        ]);

        $this->addToAssertionCount(2);
    }

    public function testValidateDecodeOptionsRejectsNonBooleanStrict(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Strict mode must be a boolean');

        ToonOptions::validateDecodeOptions([
            ToonOptions::STRICT => 1,
        ]);
    }

    public function testValidateDecodeOptionsAcceptsValidIndent(): void
    {
        ToonOptions::validateDecodeOptions([
            ToonOptions::INDENT => 2,
        ]);

        $this->addToAssertionCount(1);
    }

    public function testValidateDecodeOptionsRejectsNegativeIndent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Indent must be a non-negative integer');

        ToonOptions::validateDecodeOptions([
            ToonOptions::INDENT => -1,
        ]);
    }

    // ===== Context Extraction Tests =====

    public function testExtractFromContextWithNamespacedOptions(): void
    {
        $context = [
            'toon_options' => [
                ToonOptions::DELIMITER => ToonOptions::DELIMITER_TAB,
                ToonOptions::STRICT => false,
            ],
        ];

        $extracted = ToonOptions::extractFromContext($context);

        $this->assertEquals(ToonOptions::DELIMITER_TAB, $extracted[ToonOptions::DELIMITER]);
        $this->assertFalse($extracted[ToonOptions::STRICT]);
    }

    public function testExtractFromContextReturnsEmptyArrayForNoOptions(): void
    {
        $context = [
            'some_other_key' => 'value',
        ];

        $extracted = ToonOptions::extractFromContext($context);

        $this->assertEquals([], $extracted);
    }

    public function testExtractFromContextIgnoresNonNamespacedOptions(): void
    {
        $context = [
            'delimiter' => '|',  // Not namespaced, should be ignored
            'strict' => false,   // Not namespaced, should be ignored
            'other_option' => 123,
        ];

        $extracted = ToonOptions::extractFromContext($context);

        // Should return empty array since no 'toon_options' key
        $this->assertEquals([], $extracted);
    }

    // ===== Constants Tests =====

    public function testValidDelimitersConstant(): void
    {
        $this->assertContains(',', ToonOptions::VALID_DELIMITERS);
        $this->assertContains("\t", ToonOptions::VALID_DELIMITERS);
        $this->assertContains('|', ToonOptions::VALID_DELIMITERS);
        $this->assertCount(3, ToonOptions::VALID_DELIMITERS);
    }

    public function testDefaultEncodeOptionsConstant(): void
    {
        $defaults = ToonOptions::DEFAULT_ENCODE_OPTIONS;

        $this->assertEquals(',', $defaults[ToonOptions::DELIMITER]);
        $this->assertEquals(2, $defaults[ToonOptions::INDENT]);
        $this->assertFalse($defaults[ToonOptions::LENGTH_MARKER]);
    }

    public function testDefaultDecodeOptionsConstant(): void
    {
        $defaults = ToonOptions::DEFAULT_DECODE_OPTIONS;

        $this->assertEquals(2, $defaults[ToonOptions::INDENT]);
        $this->assertTrue($defaults[ToonOptions::STRICT]);
    }
}
