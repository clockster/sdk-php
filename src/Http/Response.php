<?php

declare(strict_types=1);

namespace Clockster\Http;

/**
 * What came back, unread. The status and the bytes are what the client above reads; the headers
 * matter for `Retry-After` and nothing else so far.
 */
final class Response
{
    /**
     * @param array<string, string> $headers keyed by the lowercased header name
     */
    public function __construct(
        public readonly int $status,
        public readonly string $body,
        public readonly array $headers = [],
    ) {
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }
}
