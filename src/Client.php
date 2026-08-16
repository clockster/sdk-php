<?php

declare(strict_types=1);

namespace Clockster;

use Clockster\Exception\ClocksterException;
use Clockster\Generated\Api;
use Clockster\Http\Caller;
use Clockster\Http\CurlTransport;
use Clockster\Http\Transport;

/**
 * The Company API, one token to one company. Everything below the constructor is generated from
 * the API's OpenAPI document.
 *
 *     $clockster = new Clockster\Client(getenv('CLOCKSTER_TOKEN'));
 *
 *     $users = $clockster->users->list(perPage: 100, include: ['location']);
 *
 * A method answers the parsed body, so rows are `$users['data']`, and a refusal is thrown rather
 * than returned — see Clockster\Exception\ApiException. Deliveries are verified with
 * Clockster\Webhooks.
 */
final class Client extends Api
{
    /** As it goes out in the User-Agent. The release workflow checks the tag against it. */
    public const VERSION = '0.1.0';

    /** Production. A demo stand answers the same API at another host. */
    public const DEFAULT_BASE_URL = 'https://api.clockster.com';

    /** Seconds, applied to each request. */
    public const DEFAULT_TIMEOUT = 30.0;

    /**
     * @param string      $token     the company API key, issued in the web application under
     *                               Settings, API
     * @param string|null $baseUrl   point at a demo stand instead of production
     * @param float       $timeout   seconds; ignored when a transport of your own is given, since
     *                               it carries its own
     * @param string|null $userAgent name your integration in our request log, which is worth doing
     *                               when several talk to the same company
     * @param Transport|null $transport send the calls some other way than curl — a PSR-18 client,
     *                                  a retrying decorator, a double in a test
     */
    public function __construct(
        string $token,
        ?string $baseUrl = null,
        float $timeout = self::DEFAULT_TIMEOUT,
        ?string $userAgent = null,
        ?Transport $transport = null,
    ) {
        if (trim($token) === '') {
            throw new ClocksterException('A company API key is required. Issue one under Settings, API.');
        }

        parent::__construct(new Caller(
            $token,
            rtrim($baseUrl ?? self::DEFAULT_BASE_URL, '/'),
            $userAgent ?? 'clockster-php/' . self::VERSION,
            $transport ?? new CurlTransport($timeout),
        ));
    }
}
