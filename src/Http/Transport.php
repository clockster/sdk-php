<?php

declare(strict_types=1);

namespace Clockster\Http;

use Clockster\Exception\TransportException;

/**
 * How the bytes travel.
 *
 * The package sends them with curl and needs nothing installed for it. Implement this to send them
 * some other way — a PSR-18 client, a recording double in a test, a queue — and hand it to the
 * client: `new Clockster\Client($token, transport: $yours)`.
 */
interface Transport
{
    /**
     * @throws TransportException when no answer came back at all, which is the one case a retry is
     *                            worth: the call may or may not have been applied
     */
    public function send(Request $request): Response;
}
