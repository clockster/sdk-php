<?php

declare(strict_types=1);

namespace Clockster\Http;

use Clockster\Exception\TransportException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Sends the calls through a PSR-18 client of yours — Guzzle, Symfony's, a retrying decorator —
 * instead of curl.
 *
 * This class is the only thing here that needs anything installed: `psr/http-client` and
 * `psr/http-factory`, plus a client implementing them. Nothing loads it unless you name it.
 *
 *     $clockster = new Clockster\Client($token, transport: new Psr18Transport(
 *         $guzzle,
 *         $requestFactory,
 *         $streamFactory,
 *     ));
 */
final class Psr18Transport implements Transport
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly RequestFactoryInterface $requests,
        private readonly StreamFactoryInterface $streams,
    ) {
    }

    public function send(Request $request): Response
    {
        $sent = $this->requests->createRequest($request->method, $request->url);

        foreach ($request->headers as $name => $value) {
            $sent = $sent->withHeader($name, $value);
        }

        if ($request->body !== null) {
            $sent = $sent->withBody($this->streams->createStream($request->body));
        }

        try {
            $answer = $this->client->sendRequest($sent);
        } catch (ClientExceptionInterface $failure) {
            throw new TransportException(
                sprintf('%s %s: %s', $request->method, $request->url, $failure->getMessage()),
                previous: $failure,
            );
        }

        $headers = [];

        foreach ($answer->getHeaders() as $name => $values) {
            $headers[strtolower($name)] = implode(', ', $values);
        }

        return new Response($answer->getStatusCode(), (string) $answer->getBody(), $headers);
    }
}
