<?php

declare(strict_types=1);

namespace Clockster\Http;

/**
 * The bytes of a file and what travels beside them. One operation carries these rather than JSON.
 */
final class Upload
{
    /**
     * @param array<string, scalar|null> $fields the form's other fields; a null one is not sent
     */
    public function __construct(
        public readonly string $contents,
        public readonly string $filename = 'upload',
        public readonly array $fields = [],
    ) {
    }
}
