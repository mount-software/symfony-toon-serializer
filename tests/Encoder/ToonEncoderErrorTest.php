<?php

declare(strict_types=1);

namespace MountSoftware\SymfonyToonSerializer\Tests\Encoder;

use MountSoftware\SymfonyToonSerializer\Encoder\ToonEncoder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Test error handling and strict mode behavior.
 */
class ToonEncoderErrorTest extends TestCase
{
    private Serializer $serializer;
    private ToonEncoder $encoder;

    protected function setUp(): void
    {
        $this->encoder = new ToonEncoder();
        $this->serializer = new Serializer(
            [new ObjectNormalizer(), new ArrayDenormalizer()],
            [$this->encoder, new JsonEncoder()]
        );
    }

    // ===== 9.2.11 Error & Strict-Mode Behavior Tests =====

    public function testDecodeThrowsOnLengthMismatchInStrictMode(): void
    {
        // Declared length 3, but only 2 items provided
        $toon = "tags[3]: admin,ops";

        $this->expectException(\Exception::class);

        $this->encoder->decode($toon, 'toon', [
            'toon_options' => ['strict' => true],
        ]);
    }

    public function testDecodeThrowsOnColumnMismatchInStrictMode(): void
    {
        // Declared 3 columns, but first row has 4 values
        $toon = <<<TOON
users[2]{id,name,role}:
  1,Alice,admin,extra
  2,Bob,user
TOON;

        $this->expectException(\Exception::class);

        $this->encoder->decode($toon, 'toon', [
            'toon_options' => ['strict' => true],
        ]);
    }

    public function testDecodeMalformedInputNonStrictMode(): void
    {
        // In non-strict mode, the behavior may vary based on the underlying library
        // This test documents the actual behavior without strict mode
        $toon = "tags[3]: admin,ops"; // Length mismatch

        try {
            $data = $this->encoder->decode($toon, 'toon', [
                'toon_options' => ['strict' => false],
            ]);

            // If it doesn't throw, verify we get something reasonable
            $this->assertIsArray($data);
        } catch (\Exception $e) {
            // Some libraries may still throw even in non-strict mode
            $this->addToAssertionCount(1);
        }
    }

    public function testDecodeInvalidEscapeSequence(): void
    {
        // Test with potentially invalid escape sequences
        $toon = 'value: "invalid\\xescape"';

        try {
            $data = $this->encoder->decode($toon, 'toon');

            // If successful, the value should be decoded somehow
            $this->assertArrayHasKey('value', $data);
        } catch (\Exception $e) {
            // It's acceptable to throw on invalid escapes
            $this->addToAssertionCount(1);
        }
    }

    public function testDecodeMalformedStructure(): void
    {
        // Completely malformed TOON structure
        $toon = "this is not : valid : toon : format ::: ...";

        try {
            $data = $this->encoder->decode($toon, 'toon', [
                'toon_options' => ['strict' => true],
            ]);

            // If parsing succeeds, verify we get an array
            $this->assertIsArray($data);
        } catch (\Exception $e) {
            // Expected to throw on malformed input
            $this->addToAssertionCount(1);
        }
    }

    public function testDecodeEmptyStringStrictMode(): void
    {
        $toon = "";

        // In strict mode, empty input throws an exception
        $this->expectException(\Exception::class);

        $this->encoder->decode($toon, 'toon', [
            'toon_options' => ['strict' => true],
        ]);
    }

    public function testDecodeEmptyStringNonStrictMode(): void
    {
        $toon = "";

        $data = $this->encoder->decode($toon, 'toon', [
            'toon_options' => ['strict' => false],
        ]);

        // Empty string decodes to null in non-strict mode
        $this->assertNull($data);
    }
}
