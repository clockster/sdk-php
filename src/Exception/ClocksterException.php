<?php

declare(strict_types=1);

namespace Clockster\Exception;

use RuntimeException;

/**
 * Everything this package throws. Catch it to catch the lot; catch ApiException for a call the API
 * refused, and TransportException for one that never got an answer.
 */
class ClocksterException extends RuntimeException
{
}
