# Symfony TOON Serializer

[![Tests](https://img.shields.io/badge/tests-passing-brightgreen)]()
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue)]()
[![License](https://img.shields.io/badge/license-MIT-blue)]()

A Symfony Serializer integration for **TOON** (Token-Oriented Object Notation) format, built on top of the [`helgesverre/toon`](https://github.com/HelgeSverre/toon) library.

TOON is a compact, human-readable data serialization format optimized for LLM contexts and token efficiency.

## Installation

Install via Composer:

```bash
composer require mountsoftware/symfony-toon-serializer
```

## Requirements

- PHP 8.1 or higher
- Symfony Serializer ^6.4 or ^7.0
- helgesverre/toon ^1.0

## Features

- 🔌 **Drop-in integration** with Symfony Serializer component
- 📦 **Format identifier**: `toon`
- ⚙️ **Configurable options**: delimiters, strict mode, indentation
- 🧪 **Comprehensive test suite**: 56 tests covering all scenarios
- 📝 **Standalone service**: Use TOON without the full serializer
- 🎯 **Thin wrapper**: Delegates all TOON logic to the proven `helgesverre/toon` library

## Quick Start

### Using with Symfony Serializer

```php
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use MountSoftware\SymfonyToonSerializer\Encoder\ToonEncoder;

$serializer = new Serializer(
    [new ObjectNormalizer()],
    [new ToonEncoder()]
);

// Serialize to TOON
$data = [
    'id' => 123,
    'name' => 'Alice',
    'active' => true,
];

$toon = $serializer->serialize($data, 'toon');
// Output:
// id: 123
// name: Alice
// active: true

// Deserialize from TOON
$decoded = $serializer->deserialize($toon, YourClass::class, 'toon');
```

### Using the Standalone Service

```php
use MountSoftware\SymfonyToonSerializer\ToonService;

$toonService = new ToonService();

// Encode
$toon = $toonService->encode([
    'user' => [
        'name' => 'Bob',
        'age' => 30,
    ],
]);

// Decode
$data = $toonService->decode($toon);
```

## Configuration

### Context Options

You can pass options via the serializer context:

```php
$toon = $serializer->serialize($data, 'toon', [
    'toon_options' => [
        'delimiter' => "\t",    // Use tab delimiter
        'strict' => true,       // Enable strict mode
        'indent' => 4,          // 4 spaces for indentation
    ],
]);
```

### Available Options

#### Encoding Options

- **`delimiter`** (string, default: `','`): Field delimiter for tabular data
  - Comma: `','`
  - Tab: `"\t"`
  - Pipe: `'|'`
- **`indent`** (int, default: `2`): Number of spaces for indentation
- **`lengthMarker`** (string|false, default: `false`): Prefix for array length markers (use `'#'` or `false`)

#### Decoding Options

- **`strict`** (bool, default: `true`): Enable strict mode validation
  - In strict mode: Validates array lengths, column counts, indentation
  - In lenient mode: More forgiving parsing
- **`indent`** (int, default: `2`): Expected indentation level

### Default Options

Set default options for the encoder:

```php
$encoder = new ToonEncoder([
    'delimiter' => '|',
    'indent' => 4,
    'strict' => false,
]);
```

Context options override default options:

```php
// Uses pipe delimiter (from defaults)
$toon1 = $encoder->encode($data, 'toon');

// Uses tab delimiter (from context)
$toon2 = $encoder->encode($data, 'toon', [
    'toon_options' => ['delimiter' => "\t"],
]);
```

## Examples

### Simple Object

```php
$data = [
    'id' => 123,
    'name' => 'Ada',
    'active' => true,
];

$toon = $serializer->serialize($data, 'toon');
```

Output:
```toon
id: 123
name: Ada
active: true
```

### Nested Object

```php
$data = [
    'user' => [
        'id' => 123,
        'name' => 'Ada',
        'meta' => [
            'active' => true,
            'score' => 9.5,
        ],
    ],
];
```

Output:
```toon
user:
  id: 123
  name: Ada
  meta:
    active: true
    score: 9.5
```

### Arrays of Primitives

```php
$data = [
    'tags' => ['admin', 'ops', 'dev'],
];
```

Output:
```toon
tags[3]: admin,ops,dev
```

### Tabular Data

```php
$data = [
    'users' => [
        ['id' => 1, 'name' => 'Alice', 'role' => 'admin'],
        ['id' => 2, 'name' => 'Bob', 'role' => 'user'],
    ],
];
```

Output:
```toon
users[2]{id,name,role}:
  1,Alice,admin
  2,Bob,user
```

### Tab-Delimited Data

```php
$data = [
    'items' => [
        ['sku' => 'A1', 'name' => 'Widget', 'qty' => 2],
        ['sku' => 'B2', 'name' => 'Gadget', 'qty' => 1],
    ],
];

$toon = $serializer->serialize($data, 'toon', [
    'toon_options' => ['delimiter' => "\t"],
]);
```

Output:
```toon
items[2	]{sku	name	qty}:
  A1	Widget	2
  B2	Gadget	1
```

### Pipe-Delimited Data

```php
$data = [
    'products' => [
        ['id' => 1, 'name' => 'Item A'],
        ['id' => 2, 'name' => 'Item B'],
    ],
];

$toon = $serializer->serialize($data, 'toon', [
    'toon_options' => ['delimiter' => '|'],
]);
```

Output:
```toon
products[2|]{id|name}:
  1|Item A
  2|Item B
```

## Symfony Integration

### Manual Service Registration

In `config/services.yaml`:

```yaml
services:
  MountSoftware\SymfonyToonSerializer\Encoder\ToonEncoder:
    arguments:
      $defaultOptions:
        delimiter: ','
        strict: true
    tags:
      - { name: serializer.encoder }

  MountSoftware\SymfonyToonSerializer\ToonService:
    arguments:
      $defaultOptions:
        delimiter: ','
```

### Auto-configuration

If using Symfony auto-configuration, the encoder will be automatically registered if tagged properly. The library is designed to work with Symfony's standard service discovery.

## Testing

Run the test suite:

```bash
composer test
```

Or with PHPUnit directly:

```bash
vendor/bin/phpunit
```

The test suite includes:
- ✅ Simple object encoding/decoding
- ✅ Nested object handling
- ✅ Primitive array inline notation
- ✅ Tabular array formats
- ✅ Mixed and heterogeneous arrays
- ✅ Delimiter variants (comma, tab, pipe)
- ✅ Quoting and special character handling
- ✅ Error handling and strict mode validation
- ✅ Empty objects and arrays
- ✅ Round-trip data integrity

## TOON Format

TOON (Token-Oriented Object Notation) is a data serialization format designed for efficiency and readability. Key features:

- **Compact**: Optimized for minimal token usage in LLM contexts
- **Human-readable**: Easy to read and write by hand
- **Structured**: Supports objects, arrays, and primitives
- **Tabular**: Efficient representation of uniform data
- **Flexible delimiters**: Choose between comma, tab, or pipe

For more details on the TOON format, see the [helgesverre/toon documentation](https://github.com/HelgeSverre/toon).

## Known Limitations

The underlying TOON library has some limitations for round-trip encoding/decoding:

1. **Mixed arrays with nested objects**: When encoding mixed arrays where list items contain objects (e.g., `[1, ['a' => 1], 'text']`), the nested object is decoded as a string rather than an associative array. This is due to how the TOON format represents inline objects in list contexts.

2. **Arrays of arrays as list items**: Similar to above, inline array notation in list items may not round-trip perfectly.

For best round-trip support, use:
- Tabular format for uniform arrays of objects
- Inline notation for simple primitive arrays
- Nested indentation for complex nested structures

## Contributing

Contributions are welcome! Please ensure:

1. All tests pass: `composer test`
2. Code follows PSR-12 standards
3. New features include tests
4. Documentation is updated

## License

MIT License. See [LICENSE](LICENSE) file for details.

## Credits

- Built by [Mount Software](https://mountsoftware.com)
- Uses [helgesverre/toon](https://github.com/HelgeSverre/toon) for TOON encoding/decoding
- Integrates with [Symfony Serializer](https://symfony.com/doc/current/components/serializer.html)

## Links

- [TOON Format Specification](https://github.com/HelgeSverre/toon)
- [Symfony Serializer Documentation](https://symfony.com/doc/current/components/serializer.html)
- [Issue Tracker](https://github.com/mountsoftware/symfony-toon-serializer/issues)
