<?php

declare(strict_types=1);

namespace Clockster\Http;

use Clockster\Exception\ApiException;
use Clockster\Exception\AuthenticationException;
use Clockster\Exception\ConflictException;
use Clockster\Exception\ForbiddenException;
use Clockster\Exception\InvalidBodyException;
use Clockster\Exception\NotFoundException;
use Clockster\Exception\RateLimitException;
use Clockster\Exception\ServerException;
use Clockster\Exception\TransportException;
use Clockster\Exception\ValidationException;
use Clockster\Validator;
use JsonException;

/**
 * Everything between a generated method and the transport.
 *
 * One place decides how a query parameter is written, where the token goes and what a refusal
 * becomes, so the generated half is the operations and nothing else.
 */
final class Caller
{
    /** @var array<int, class-string<ApiException>> */
    private const REFUSALS = [
        401 => AuthenticationException::class,
        403 => ForbiddenException::class,
        404 => NotFoundException::class,
        409 => ConflictException::class,
        422 => ValidationException::class,
    ];

    public function __construct(
        private readonly string $token,
        private readonly string $baseUrl,
        private readonly string $userAgent,
        private readonly Transport $transport,
        private readonly bool $validate = false,
    ) {
    }

    /**
     * @param array<string, mixed>      $query
     * @param array<string, mixed>|null $body
     *
     * @return array<string, mixed> the parsed answer, or an empty array where there was no body
     *
     * @throws ApiException         when the API refused the call
     * @throws TransportException   when no answer came back, or one that is not JSON
     * @throws InvalidBodyException where the client was built to read a body first, and this one
     *                              is not what the document describes
     */
    public function call(
        string $method,
        string $path,
        array $query = [],
        ?array $body = null,
        ?Upload $upload = null,
        ?string $idempotencyKey = null,
    ): array {
        // Only where a caller asked for it, and nothing goes out when it says no. See
        // Clockster\Validator for why that is off by default.
        if ($this->validate && $body !== null) {
            Validator::check($method, $path, $body);
        }

        $headers = [
            // Read per call rather than held from construction, so a rotated key does not need a
            // new client.
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
            'User-Agent' => $this->userAgent,
        ];

        if ($idempotencyKey !== null) {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }

        $written = $this->payload($body, $upload, $headers);
        $target = $this->baseUrl . $path . $this->query($query);

        return $this->answer($this->transport->send(new Request($method, $target, $headers, $written)));
    }

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, string>     $headers
     */
    private function payload(?array $body, ?Upload $upload, array &$headers): ?string
    {
        if ($upload !== null) {
            $boundary = 'clockster' . bin2hex(random_bytes(16));
            $headers['Content-Type'] = 'multipart/form-data; boundary=' . $boundary;

            return $this->multipart($upload, $boundary);
        }

        if ($body === null) {
            return null;
        }

        $headers['Content-Type'] = 'application/json';

        try {
            return json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (JsonException $failure) {
            throw new TransportException('The body cannot be written as JSON: ' . $failure->getMessage(), previous: $failure);
        }
    }

    private function multipart(Upload $upload, string $boundary): string
    {
        $written = '';

        foreach ($upload->fields as $name => $value) {
            // An omitted field is omitted rather than sent empty.
            if ($value === null) {
                continue;
            }

            $written .= sprintf(
                "--%s\r\nContent-Disposition: form-data; name=\"%s\"\r\n\r\n%s\r\n",
                $boundary,
                $name,
                is_bool($value) ? ($value ? 'true' : 'false') : (string) $value,
            );
        }

        $written .= sprintf(
            "--%s\r\nContent-Disposition: form-data; name=\"file\"; filename=\"%s\"\r\n"
            . "Content-Type: application/octet-stream\r\n\r\n%s\r\n",
            $boundary,
            $upload->filename,
            $upload->contents,
        );

        return $written . sprintf("--%s--\r\n", $boundary);
    }

    /**
     * Query parameters as this API reads them.
     *
     * A list travels comma-separated rather than as a repeated `field[]` — both are accepted, and
     * this is the one the document describes. A parameter left null is not sent at all: an omitted
     * filter and an empty one mean different things.
     *
     * @param array<string, mixed> $query
     */
    private function query(array $query): string
    {
        $written = [];

        foreach ($query as $name => $value) {
            if ($value === null || (is_array($value) && $value === [])) {
                continue;
            }

            $written[] = rawurlencode($name) . '=' . rawurlencode($this->scalar($value));
        }

        return $written === [] ? '' : '?' . implode('&', $written);
    }

    private function scalar(mixed $value): string
    {
        if (is_array($value)) {
            return implode(',', array_map(fn (mixed $item): string => $this->scalar($item), $value));
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value) || is_string($value)) {
            return (string) $value;
        }

        // Nothing the document describes reaches here; writing it as JSON beats writing nothing.
        return (string) json_encode($value);
    }

    /**
     * @return array<string, mixed>
     */
    private function answer(Response $response): array
    {
        if ($response->status >= 400) {
            throw $this->refusal($response);
        }

        // A 204 has no body, and neither has a call that answers nothing.
        if (trim($response->body) === '') {
            return [];
        }

        try {
            $answer = json_decode($response->body, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $failure) {
            throw new TransportException('The answer is not JSON: ' . $failure->getMessage(), previous: $failure);
        }

        if (!is_array($answer)) {
            throw new TransportException('The answer is JSON but not an object.');
        }

        /** @var array<string, mixed> $answer */
        return $answer;
    }

    private function refusal(Response $response): ApiException
    {
        // An edge refusal can answer before the application does, and need not be JSON.
        $body = json_decode($response->body, true);
        $error = is_array($body) && is_array($body['error'] ?? null) ? $body['error'] : [];

        $code = is_string($error['code'] ?? null) ? $error['code'] : 'unknown';
        $message = is_string($error['message'] ?? null)
            ? $error['message']
            : sprintf('The API answered %d.', $response->status);
        $requestId = is_string($error['request_id'] ?? null) ? $error['request_id'] : null;

        /** @var array<string, list<string>> $errors */
        $errors = is_array($error['errors'] ?? null) ? $error['errors'] : [];

        if ($response->status === 429) {
            $retry = $response->header('Retry-After');

            return new RateLimitException(
                $message,
                $response->status,
                $code,
                $requestId,
                $errors,
                $body,
                $retry !== null && ctype_digit($retry) ? (int) $retry : null,
            );
        }

        $kind = self::REFUSALS[$response->status] ?? ($response->status >= 500 ? ServerException::class : ApiException::class);

        return new $kind($message, $response->status, $code, $requestId, $errors, $body);
    }
}
