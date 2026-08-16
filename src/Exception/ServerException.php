<?php

declare(strict_types=1);

namespace Clockster\Exception;

/**
 * 5xx — ours to fix. Retrying is safe on a read and on anything carrying an idempotency key.
 */
final class ServerException extends ApiException
{
}
