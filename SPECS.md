
# symfony-toon-serializer – Library Specification

## 1. Purpose

This document specifies a Composer library that adds **TOON** (Token-Oriented Object Notation) support to the **Symfony Serializer** component by wrapping the existing PHP TOON implementation.

The library is a *thin integration layer*:
- It depends on the existing TOON PHP library (`helgesverre/toon`).
- It exposes a Symfony Serializer encoder/decoder for the `toon` format.
- It provides a small helper service for direct TOON encode/decode usage outside the serializer, if desired.
- It ships with a comprehensive PHPUnit test suite for encoding/decoding behavior.

---

## 2. Package Scope & Requirements

### 2.1 Package Name (Composer)

Proposed Composer package name:

```json
{
  "name": "mountsoftware/symfony-toon-serializer",
  "description": "Symfony Serializer integration for TOON (Token-Oriented Object Notation) on top of helgesverre/toon",
  "type": "library"
}
```

### 2.2 Runtime Requirements

- **PHP**: 8.1+ (or higher, depending on project standards).
- **Symfony Serializer**: `symfony/serializer` ^6.4 or ^7.0.
- **TOON Library**: `helgesverre/toon` (latest stable version).

### 2.3 Installation

End users should be able to install the library via:

```bash
composer require mountsoftware/symfony-toon-serializer
```

The library’s `composer.json` must declare `helgesverre/toon` and `symfony/serializer` as dependencies:

```json
{
  "require": {
    "php": ">=8.1",
    "symfony/serializer": "^6.4 || ^7.0",
    "helgesverre/toon": "^1.0" // or current stable
  }
}
```

---

## 3. High-Level Design

### 3.1 Objectives

1. Register a **`toon` format** with the Symfony Serializer via a custom encoder/decoder.
2. Delegate the actual encoding/decoding logic to the `HelgeSverre\Toon\Toon` class from `helgesverre/toon`.
3. Provide a small TOON helper service that can be used by other application layers.
4. Offer extensive unit test coverage for:
   - Serialization (PHP data → TOON string)
   - Deserialization (TOON string → PHP data)
   - Round-trip scenarios
   - Error and strict-mode behavior

### 3.2 Data Model

The library must treat TOON as a drop-in replacement for JSON at the data-model level:

- **Scalars**: string, int, float, bool, null
- **Associative arrays / objects** (key → value)
- **Indexed arrays** (lists) of scalars, objects, and nested structures

TOON is just a different wire format for the same conceptual data model.

---

## 4. Public API & Usage

### 4.1 Symfony Serializer Integration

#### 4.1.1 Basic Usage

Consumers should be able to use the TOON format via the standard `SerializerInterface`:

```php
use Symfony\Component\Serializer\SerializerInterface;

class ToonExampleService
{
    public function __construct(private SerializerInterface $serializer) {}

    public function exportToToon(mixed $data): string
    {
        return $this->serializer->serialize($data, 'toon');
    }

    public function importFromToon(string $payload, string $type = 'array'): mixed
    {
        // $type can be an FQCN for objects, e.g. App\Dto\UserDto
        return $this->serializer->deserialize($payload, $type, 'toon');
    }
}
```

#### 4.1.2 Supported Format Name

- The format identifier must be the literal string: `toon`.

### 4.2 Standalone TOON Helper Service

Optionally expose a helper around the underlying TOON implementation:

```php
namespace YourVendor\SymfonyToonSerializer;

use HelgeSverre\Toon\Toon;

final class ToonService
{
    public function __construct(private array $defaultOptions = [])
    {
    }

    public function encode(mixed $data, array $options = []): string
    {
        $options = array_replace($this->defaultOptions, $options);

        return Toon::encode($data, $options);
    }

    public function decode(string $toon, array $options = []): mixed
    {
        $options = array_replace($this->defaultOptions, $options);

        return Toon::decode($toon, $options);
    }
}
```

This service is not strictly required by Symfony but is useful for other components.

---

## 5. Library Structure

### 5.1 Directory Layout

```text
symfony-toon-serializer/
├─ src/
│  ├─ Encoder/
│  │  └─ ToonEncoder.php
│  ├─ ToonService.php
│  └─ (optional) DependencyInjection/ & Bundle/
├─ tests/
│  ├─ Encoder/
│  │  └─ ToonEncoderTest.php
│  └─ ToonServiceTest.php
├─ composer.json
└─ README.md
```

### 5.2 Namespaces

- Root namespace: `YourVendor\SymfonyToonSerializer`
- Encoder namespace: `YourVendor\SymfonyToonSerializer\Encoder`
- Optional bundle integration (if implemented):
  - `YourVendor\SymfonyToonSerializer\DependencyInjection`
  - `YourVendor\SymfonyToonSerializer\SymfonyToonSerializerBundle`

---

## 6. Encoder / Decoder Specification

### 6.1 Class Signature

```php
namespace YourVendor\SymfonyToonSerializer\Encoder;

use Symfony\Component\Serializer\Encoder\ContextAwareEncoderInterface;
use Symfony\Component\Serializer\Encoder\ContextAwareDecoderInterface;
use HelgeSverre\Toon\Toon;

final class ToonEncoder implements ContextAwareEncoderInterface, ContextAwareDecoderInterface
{
    public const FORMAT = 'toon';

    public function __construct(private array $defaultOptions = [])
    {
    }

    public function supportsEncoding(string $format, array $context = []): bool
    {
        return $format === self::FORMAT;
    }

    public function encode($data, string $format, array $context = []): string
    {
        $options = array_replace($this->defaultOptions, $context['toon_options'] ?? []);

        return Toon::encode($data, $options);
    }

    public function supportsDecoding(string $format, array $context = []): bool
    {
        return $format === self::FORMAT;
    }

    public function decode(string $data, string $format, array $context = [])
    {
        $options = array_replace($this->defaultOptions, $context['toon_options'] ?? []);

        return Toon::decode($data, $options);
    }
}
```

### 6.2 Context & Options

The encoder/decoder must support configuration via context:

- `toon_options`: array passed through to `Toon::encode` / `Toon::decode`.
- Common options that SHOULD be supported if the underlying library exposes them (examples):
  - `delimiter` (default: `,`)
  - `strict` (boolean, default: `true`)
  - optional key-folding / path-expansion flags if available

Example usage:

```php
$toon = $serializer->serialize($data, 'toon', [
    'toon_options' => [
        'delimiter' => "	",
        'strict' => true,
    ],
]);
```

The encoder must not make assumptions beyond what the underlying TOON library supports; it simply forwards options.

### 6.3 Data Type Handling

The encoder must rely on Symfony normalizers to convert objects to arrays/scalars. It receives normalized data such as:

- Scalars (string, int, float, bool, null)
- Arrays (indexed and associative)
- Nested structures

The encoder then passes this data to `Toon::encode`.

The decoder must return a normalized PHP value that Symfony’s denormalizers can then transform into objects when `deserialize` is used with a target class.

---

## 7. Symfony Wiring

### 7.1 Manual Service Registration

For projects without an auto-discovery mechanism, document manual service registration:

```yaml
# config/services.yaml
services:
  YourVendor\SymfonyToonSerializer\Encoder\ToonEncoder:
    arguments:
      $defaultOptions: { }
    tags:
      - { name: serializer.encoder }
```

Optional registration of `ToonService`:

```yaml
services:
  YourVendor\SymfonyToonSerializer\ToonService:
    arguments:
      $defaultOptions: { }
```

### 7.2 Optional Symfony Bundle

Optionally, provide a small bundle to auto-register the encoder and expose configuration:

- Bundle class: `YourVendor\SymfonyToonSerializer\SymfonyToonSerializerBundle`
- Configuration root key: `your_vendor_toon`

Example configuration (if implemented):

```yaml
your_vendor_toon:
  default_options:
    delimiter: ','
    strict: true
```

The bundle’s extension should read these options and pass them as `$defaultOptions` to both `ToonEncoder` and `ToonService`.

---

## 8. Encoding/Decoding Behavior Specification

### 8.1 Objectives

The encoder/decoder must ensure:

- **Correct mapping** of PHP data to valid TOON text.
- **Correct parsing** of valid TOON text to the equivalent PHP structure.
- **Round-trip safety** for supported data shapes (encode → decode → data).
- **Clear error behavior** for malformed inputs when strict mode is enabled.

### 8.2 Data Mappings (Conceptual)

At a high level, the mapping should respect the TOON specification:

- **Primitive values** map directly:
  - Strings, integers, floats, booleans, and null.
- **Objects / associative arrays** map to TOON key-value block structures with indentation.
- **Lists of primitives** may use compact inline array headers.
- **Lists of uniform objects** may use TOON’s tabular notation.
- **Heterogeneous lists** may fall back to list notation with `-` items.
- **Nested structures** must be preserved via indentation and nested blocks.

Exact textual details are delegated to `helgesverre/toon`, but unit tests (below) must enforce expected formats for representative cases.

---

## 9. Test Suite Specification

The library must ship with PHPUnit tests that cover both the encoder and the underlying behavior via the encoder and/or ToonService.

### 9.1 Test Infrastructure

- Use PHPUnit as the test runner.
- Provide a `phpunit.xml.dist` at the package root.
- Use namespaces mirroring `src/` under `tests/`.
- Where practical, use Symfony’s `Serializer` instance with actual normalizers and the `ToonEncoder` registered.

### 9.2 Test Strategy

For each scenario, create at least:

- A **serialization** test (PHP data → TOON string).
- A **deserialization** test (TOON string → PHP data).
- When relevant, a **round-trip** test (data → TOON → data).

#### 9.2.1 Simple Object

PHP input:

```php
$data = [
    'id' => 123,
    'name' => 'Ada',
    'active' => true,
];
```

Expected TOON (approximate canonical form):

```toon
id: 123
name: Ada
active: true
```

Tests:

- `testEncodeSimpleObject`
- `testDecodeSimpleObject`
- `testRoundTripSimpleObject`

#### 9.2.2 Nested Object

PHP input:

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

Expected TOON:

```toon
user:
  id: 123
  name: Ada
  meta:
    active: true
    score: 9.5
```

Tests:

- `testEncodeNestedObject`
- `testDecodeNestedObject`
- `testRoundTripNestedObject`

#### 9.2.3 Primitive Arrays (Inline Form)

PHP input:

```php
$data = [
    'tags' => ['admin', 'ops', 'dev'],
];
```

Expected TOON:

```toon
tags[3]: admin,ops,dev
```

Tests:

- `testEncodePrimitiveArrayInline`
- `testDecodePrimitiveArrayInline`
- `testRoundTripPrimitiveArrayInline`

#### 9.2.4 Tabular Arrays of Objects

PHP input:

```php
$data = [
    'users' => [
        ['id' => 1, 'name' => 'Alice', 'role' => 'admin'],
        ['id' => 2, 'name' => 'Bob',   'role' => 'user'],
    ],
];
```

Expected TOON (ideal form):

```toon
users[2]{id,name,role}:
  1,Alice,admin
  2,Bob,user
```

Also test strings requiring quoting, e.g. names with spaces or commas.

Tests:

- `testEncodeTabularArrayOfObjects`
- `testDecodeTabularArrayOfObjects`
- `testRoundTripTabularArrayOfObjects`

#### 9.2.5 Mixed & Non-Uniform Arrays

PHP input:

```php
$data = [
    'items' => [1, ['a' => 1], 'text'],
];
```

Expected TOON (generic list representation):

```toon
items[3]:
  - 1
  - a: 1
  - text
```

Tests:

- `testEncodeMixedArray`
- `testDecodeMixedArray`
- `testRoundTripMixedArray`

#### 9.2.6 Arrays of Arrays

PHP input:

```php
$data = [
    'pairs' => [
        [1, 2],
        [3, 4],
    ],
];
```

Expected TOON:

```toon
pairs[2]:
  - [2]: 1,2
  - [2]: 3,4
```

Tests:

- `testEncodeArrayOfArrays`
- `testDecodeArrayOfArrays`
- `testRoundTripArrayOfArrays`

#### 9.2.7 Empty Objects & Arrays

Cases to test:

- Root empty object: `[]` or `(object) []`.
- Nested empty object:

  ```php
  ['config' => []]
  ```

- Empty arrays:

  ```php
  ['items' => []]
  ```

Ensure the encoder/decoder handle these without errors. The exact textual representation may follow the TOON library’s default; tests should assert consistency and round-trip correctness.

Tests:

- `testEncodeEmptyObject`
- `testDecodeEmptyObject`
- `testEncodeEmptyArray`
- `testDecodeEmptyArray`

#### 9.2.8 Delimiter Variants

Use the context options to assert that different delimiters are honored.

Example PHP input:

```php
$data = [
    'items' => [
        ['sku' => 'A1', 'name' => 'Widget', 'qty' => 2, 'price' => 9.99],
        ['sku' => 'B2', 'name' => 'Gadget', 'qty' => 1, 'price' => 14.5],
    ],
];
```

- Test with default comma delimiter.
- Test with tab delimiter `"	"`.
- Test with pipe delimiter `"|"`.

For each delimiter, ensure the encoder and decoder agree and round-trip correctly.

Tests:

- `testEncodeWithTabDelimiter`
- `testDecodeWithTabDelimiter`
- `testEncodeWithPipeDelimiter`
- `testDecodeWithPipeDelimiter`

#### 9.2.9 Quoting & Edge Cases

Create cases where strings resemble other types or contain special characters:

PHP input:

```php
$data = [
    'empty'    => '',
    'boolLike' => 'true',
    'numLike'  => '05',
    'colon'    => 'a:b',
    'spacey'   => '  leading',
    'dash'     => '-value',
    'emoji'    => 'Hello 🌍',
];
```

Tests must verify that:

- The encoder produces a TOON representation that decodes back to the same PHP values.
- The decoder can correctly handle quoted strings and escapes as produced by the underlying library.

Tests:

- `testEncodeQuotingRules`
- `testDecodeQuotingRules`
- `testRoundTripQuotingRules`

#### 9.2.10 Root Forms

Test behavior when encoding/decoding root forms directly:

- Root primitive: `42`, `"hello"`, `true`.
- Root array: e.g. `['a', 'b', 'c']`.
- Root object: as in previous scenarios.

Tests:

- `testEncodeRootPrimitive`
- `testDecodeRootPrimitive`
- `testEncodeRootArray`
- `testDecodeRootArray`
- `testRoundTripRootForms`

#### 9.2.11 Error & Strict-Mode Behavior

Create tests for malformed TOON strings to ensure strict-mode behavior is well-defined:

- Declared array length mismatch.
- Incorrect number of columns in tabular rows.
- Invalid or unsupported escape sequences in quoted strings.
- Other structural errors as supported by `helgesverre/toon`.

In strict mode:

- Decoding must throw an exception (either the underlying TOON exception or a meaningful wrapper).

In non-strict or default mode (if supported):

- Behavior should be documented and tested (either still throws, or attempts best-effort parsing).

Tests:

- `testDecodeThrowsOnLengthMismatchInStrictMode`
- `testDecodeThrowsOnInvalidEscapeInStrictMode`
- `testDecodeMalformedInputNonStrictMode` (if applicable)

---

## 10. Quality & DX Requirements

- Code style: follow PSR-12.
- Provide full PHPDoc for public methods and classes.
- Include a clear `README.md` with:
  - Installation instructions.
  - Usage examples for `SerializerInterface` with `toon` format.
  - Configuration examples (context options, strict mode, delimiters).
- Include CI configuration examples (e.g. GitHub Actions) in the documentation or repository to run tests on supported PHP/Symfony versions.

---

## 11. Acceptance Criteria

The library is considered complete when:

1. It is installable via Composer and requires `helgesverre/toon` and `symfony/serializer`.
2. `SerializerInterface` can `serialize()` and `deserialize()` using the `toon` format without additional user code.
3. TOON encode/decode options can be provided via serializer context (`toon_options`) and (optionally) global defaults.
4. All unit tests described in this document are implemented and pass.
5. The implementation is a thin wrapper around `helgesverre/toon` with no re-implementation of core TOON parsing/encoding logic.
6. Documentation (README.md + this spec) is present and up to date.
