<?php

declare(strict_types=1);

namespace Clockster\Exception;

/**
 * 409 — the row is there and cannot be changed the way you asked.
 */
final class ConflictException extends ApiException
{
}
