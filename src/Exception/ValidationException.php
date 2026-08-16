<?php

declare(strict_types=1);

namespace Clockster\Exception;

/**
 * 422 — the request was understood and refused. `errors` names the fields.
 *
 * A batch is all or nothing: a 422 means none of it landed, so the data is fixable and the whole
 * call can be repeated.
 */
final class ValidationException extends ApiException
{
}
