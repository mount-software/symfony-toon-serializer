<?php

declare(strict_types=1);

namespace MountSoftware\SymfonyToonSerializer\Tests;

use MountSoftware\SymfonyToonSerializer\ToonService;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for ToonService standalone helper.
 */
class ToonServiceTest extends TestCase
{
    private ToonService $service;

    protected function setUp(): void
    {
        $this->service = new ToonService();
    }

    public function testEncodeBasicData(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Test',
        ];

        $toon = $this->service->encode($data);

        $this->assertStringContainsString('id: 1', $toon);
        $this->assertStringContainsString('name: Test', $toon);
    }

    public function testDecodeBasicData(): void
    {
        $toon = "id: 1\nname: Test";

        $data = $this->service->decode($toon);

        $this->assertEquals(['id' => 1, 'name' => 'Test'], $data);
    }

    public function testRoundTrip(): void
    {
        $original = [
            'user' => [
                'id' => 123,
                'name' => 'Alice',
                'tags' => ['admin', 'developer'],
            ],
        ];

        $toon = $this->service->encode($original);
        $decoded = $this->service->decode($toon);

        $this->assertEquals($original, $decoded);
    }

    public function testEncodeWithOptions(): void
    {
        $data = [
            'items' => [
                ['a' => 1, 'b' => 2],
                ['a' => 3, 'b' => 4],
            ],
        ];

        $toon = $this->service->encode($data, ['delimiter' => '|']);

        $this->assertStringContainsString('|', $toon);
    }

    public function testDecodeWithOptions(): void
    {
        // The decoder auto-detects delimiter from the TOON format
        // For pipe-delimited tabular data, field names also use the delimiter
        $toon = "items[2|]{a|b}:\n  1|2\n  3|4";

        $data = $this->service->decode($toon);

        $this->assertCount(2, $data['items']);
        $this->assertEquals(['a' => 1, 'b' => 2], $data['items'][0]);
        $this->assertEquals(['a' => 3, 'b' => 4], $data['items'][1]);
    }

    public function testServiceWithDefaultOptions(): void
    {
        $service = new ToonService(['delimiter' => "\t"]);

        $data = [
            'items' => [
                ['x' => 1, 'y' => 2],
                ['x' => 3, 'y' => 4],
            ],
        ];

        $toon = $service->encode($data);

        $this->assertStringContainsString("\t", $toon);
    }

    public function testDefaultOptionsCanBeOverridden(): void
    {
        $service = new ToonService(['delimiter' => "\t"]);

        $data = [
            'items' => [
                ['x' => 1, 'y' => 2],
                ['x' => 3, 'y' => 4],
            ],
        ];

        // Override default tab delimiter with comma
        $toon = $service->encode($data, ['delimiter' => ',']);

        $this->assertStringContainsString(',', $toon);
    }

    public function testEncodeComplexNestedStructure(): void
    {
        $data = [
            'company' => [
                'name' => 'ACME Corp',
                'departments' => [
                    [
                        'name' => 'Engineering',
                        'employees' => [
                            ['id' => 1, 'name' => 'Alice'],
                            ['id' => 2, 'name' => 'Bob'],
                        ],
                    ],
                    [
                        'name' => 'Sales',
                        'employees' => [
                            ['id' => 3, 'name' => 'Charlie'],
                        ],
                    ],
                ],
            ],
        ];

        $toon = $this->service->encode($data);
        $decoded = $this->service->decode($toon);

        $this->assertEquals($data, $decoded);
    }

    public function testEncodeSpecialCharacters(): void
    {
        $data = [
            'message' => 'Hello, World! 🌍',
            'special' => 'Quotes: "test", Newline: \n, Tab: \t',
        ];

        $toon = $this->service->encode($data);
        $decoded = $this->service->decode($toon);

        $this->assertEquals($data, $decoded);
    }

    public function testEncodeNumericTypes(): void
    {
        $data = [
            'integer' => 42,
            'float' => 3.14159,
            'negative' => -100,
            'zero' => 0,
        ];

        $toon = $this->service->encode($data);
        $decoded = $this->service->decode($toon);

        $this->assertEquals($data, $decoded);
    }

    public function testEncodeBooleanAndNull(): void
    {
        $data = [
            'isTrue' => true,
            'isFalse' => false,
            'isNull' => null,
        ];

        $toon = $this->service->encode($data);
        $decoded = $this->service->decode($toon);

        $this->assertTrue($decoded['isTrue']);
        $this->assertFalse($decoded['isFalse']);
        $this->assertNull($decoded['isNull']);
    }
}
