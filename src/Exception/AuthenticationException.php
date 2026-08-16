<?php

declare(strict_types=1);

namespace Clockster\Exception;

/**
 * 401 — no token, or one this surface does not accept.
 */
final class AuthenticationException extends ApiException
{
}
