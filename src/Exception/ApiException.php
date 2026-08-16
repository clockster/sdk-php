<?php

declare(strict_types=1);

namespace Clockster\Exception;

/**
 * A call the API refused.
 *
 * code() is what to branch on: it names the reason and does not change, where getMessage() is
 * prose and may. requestId() identifies this exact call in our logs and is worth quoting when
 * asking us about it.
 *
 *     try {
 *         $clockster->users->upsert(['users' => $people]);
 *     } catch (ValidationException $refused) {
 *         report($refused->code(), $refused->errors(), $refused->requestId());
 *     }
 *
 * The fields are methods rather than properties because \Exception already holds a $code of its
 * own, an int, which getCode() answers with — here that is the HTTP status.
 */
class ApiException extends ClocksterException
{
    /**
     * @param array<string, list<string>> $errors named fields, on a refusal that names any
     * @param mixed                       $body   the answer as it arrived, for a refusal this
     *                                            package does not know the shape of
     */
    public function __construct(
        string $message,
        private readonly int $status,
        private readonly string $reason,
        private readonly ?string $requestId = null,
        private readonly array $errors = [],
        private readonly mixed $body = null,
    ) {
        parent::__construct($this->stamped($message), $status);
    }

    /** The HTTP status, which getCode() answers with as well. */
    public function status(): int
    {
        return $this->status;
    }

    /** What went wrong, e.g. `validation_failed`. Branch on this rather than on the message. */
    public function code(): string
    {
        return $this->reason;
    }

    /** Identifies this call in our logs. Quote it when asking us about one. */
    public function requestId(): ?string
    {
        return $this->requestId;
    }

    /**
     * The fields a 422 refused, empty otherwise.
     *
     * @return array<string, list<string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /** The answer as it arrived. */
    public function body(): mixed
    {
        return $this->body;
    }

    private function stamped(string $message): string
    {
        $stamped = sprintf('[%d %s] %s', $this->status, $this->reason, $message);

        return $this->requestId === null ? $stamped : $stamped . sprintf(' (request_id %s)', $this->requestId);
    }
}
