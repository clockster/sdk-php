<?php

declare(strict_types=1);

namespace Clockster\Exception;

/**
 * A body the document says is wrong, refused here rather than by the API.
 *
 * Thrown only by a client built with `validate: true`. Nothing was sent and nothing was reached, so
 * there is no status to read and no request id to quote — where a ValidationException means the API
 * saw the call and said no, this means it never saw it.
 *
 * `errors()` is shaped exactly as ValidationException::errors() is, the fields and what is wrong
 * with each, so one handler reads a refusal from either side:
 *
 *     try {
 *         $clockster->users->upsert(['users' => $people]);
 *     } catch (InvalidBodyException | ValidationException $refused) {
 *         report($refused->errors());
 *     }
 */
final class InvalidBodyException extends ClocksterException
{
    /** @param array<string, list<string>> $errors the fields, in the order they were read */
    public function __construct(private readonly string $route, private readonly array $errors)
    {
        parent::__construct($this->stated());
    }

    /**
     * The fields, and what the document says about each.
     *
     * @return array<string, list<string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /** The operation this body was written for, as the document names it. */
    public function route(): string
    {
        return $this->route;
    }

    /** The first few, in full. A batch of a hundred with one thing wrong in each is not a message. */
    private function stated(): string
    {
        $said = [];

        foreach ($this->errors as $field => $problems) {
            foreach ($problems as $problem) {
                $said[] = $field . ' ' . $problem;
            }
        }

        $first = array_slice($said, 0, 3);
        $rest = count($said) - count($first);

        return sprintf(
            'Nothing was sent: %s does not take this body. %s%s',
            $this->route,
            implode(' ', $first),
            $rest > 0 ? sprintf(' And %d more.', $rest) : '',
        );
    }
}
