<?php

declare(strict_types=1);

namespace Clockster\Http;

/**
 * One call, as far as a transport is concerned: a method, a URL, headers and bytes.
 *
 * Everything above this — where the token goes, how a query parameter is written, how multipart is
 * encoded — is settled before a transport sees it, so a transport only sends and answers.
 */
final class Request
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        public readonly string $method,
        public readonly string $url,
        public readonly array $headers,
        public readonly ?string $body = null,
    ) {
    }
}
