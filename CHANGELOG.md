# Changelog

## 0.3.0

### May break your build (types only)

Nothing. The five values are additive.

### Changed in the API

Reaches you whether or not you update this package.

- A document's `type` also takes `driver_license`, `birth_certificate`, `marriage_certificate`,
  `divorce_certificate` and `change_fio_certificate` — in the `types` filter and in
  `POST /documents/upsert`.

### New

- A constant per new value on `DocumentsType`, and the five in `DocumentsType::values()`.

## 0.2.0

### May break your build (types only)

Nothing changes on the wire. Defer with `phpstan-baseline.neon` if you need to.

- `role`, `gender`, `locale`, `employment`, `type`, `status`, `leave_type` and `employment_type`
  were `string` and are now named sets. Use a constant (`UsersRole::EMPLOYEE`) or narrow your type.
- `links.first` and `links.last` were `mixed` and are now `null`, so a `!== null` check on either
  now reads as always false.
- `radius` no longer accepts `null`, and `priority` is now `0|1`.

### Changed in the API

Reaches you whether or not you update this package.

- A location's `radius` must be between 50 and 700. A value outside that is refused.
- `radius` cannot be cleared. Leave the key out to keep what is stored.
- `?employment=` takes only the ten terms of the set. An unknown one is refused rather than
  answering an empty page.

### New

- 27 classes of constants under `Clockster\Generated\Enum`, one per set of values.
- `Clockster\Write::filled()` drops the keys holding an empty string, so a blank cell in a file does
  not overwrite a stored value.
- `new Client($token, validate: true)` reads a body against the document and refuses one it says is
  wrong without sending it. Off by default.
- Every request body field carries its description in the docblock of the method that takes it.
