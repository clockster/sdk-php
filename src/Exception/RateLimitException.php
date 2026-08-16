<?php

declare(strict_types=1);

namespace Clockster\Exception;

/**
 * 429 — over the limit, which is 100 requests a minute counted against the token and shared by
 * every process using it.
 */
final class RateLimitException extends ApiException
{
    /**
     * @param array<string, list<string>> $errors
     * @param int|null                    $retryAfter seconds to wait, when the answer said
     */
    public function __construct(
        string $message,
        int $status,
        string $reason,
        ?string $requestId = null,
        array $errors = [],
        mixed $body = null,
        private readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message, $status, $reason, $requestId, $errors, $body);
    }

    /** Seconds to wait before trying again, from the Retry-After header. */
    public function retryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
