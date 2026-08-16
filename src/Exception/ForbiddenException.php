<?php

declare(strict_types=1);

namespace Clockster\Exception;

/**
 * 403 — a token without the ability this call needs.
 */
final class ForbiddenException extends ApiException
{
}
