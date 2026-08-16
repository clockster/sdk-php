<?php

declare(strict_types=1);

namespace Clockster\Tests;

use Clockster\Http\Request;
use Clockster\Http\Response;
use Clockster\Http\Transport;

/**
 * A transport that answers what a test told it to and keeps what it was given. Nothing here talks
 * to the API, which is the point: it is the calls this package builds that are under test.
 */
final class RecordingTransport implements Transport
{
    /** @var list<Request> */
    public array $seen = [];

    /** @var list<Response> */
    private array $answers;

    public function __construct(Response ...$answers)
    {
        $this->answers = array_values($answers);
    }

    /** @param array<string, string> $headers */
    public static function answering(string $body, int $status = 200, array $headers = []): self
    {
        return new self(new Response($status, $body, $headers));
    }

    public function send(Request $request): Response
    {
        $this->seen[] = $request;

        // The last answer stands for every call after it, so a listing can be walked with one.
        return $this->answers[count($this->seen) - 1] ?? $this->answers[count($this->answers) - 1];
    }

    public function first(): Request
    {
        return $this->seen[0];
    }

    /** @return array<string, string> the query of a recorded call, by parameter name */
    public function query(int $index = 0): array
    {
        $parts = parse_url($this->seen[$index]->url, PHP_URL_QUERY);

        if (!is_string($parts)) {
            return [];
        }

        parse_str($parts, $query);

        /** @var array<string, string> $query */
        return $query;
    }

    /** @return array<string, mixed> the body of a recorded call, parsed */
    public function body(int $index = 0): array
    {
        $body = $this->seen[$index]->body ?? '';
        /** @var array<string, mixed>|null $parsed */
        $parsed = json_decode($body, true);

        return is_array($parsed) ? $parsed : [];
    }
}
