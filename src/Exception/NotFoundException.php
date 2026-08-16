<?php

declare(strict_types=1);

namespace Clockster\Exception;

/**
 * 404 — no such row in the calling company. Another company's id answers this rather than 403,
 * so you cannot learn that it exists.
 */
final class NotFoundException extends ApiException
{
}
