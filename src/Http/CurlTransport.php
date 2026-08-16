<?php

declare(strict_types=1);

namespace Clockster\Http;

use Clockster\Exception\TransportException;

/**
 * The transport the package uses unless it is given another one: curl, which every PHP has.
 */
final class CurlTransport implements Transport
{
    public function __construct(private readonly float $timeout = 30.0)
    {
    }

    public function send(Request $request): Response
    {
        $handle = curl_init();

        if ($handle === false) {
            throw new TransportException('curl could not be started.');
        }

        /** @var array<string, string> $headers */
        $headers = [];

        curl_setopt_array($handle, $this->options($request, $headers));

        $body = curl_exec($handle);
        $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $failure = curl_error($handle);

        curl_close($handle);

        if ($body === false || $failure !== '') {
            // No answer came back, so whether the call was applied is unknown rather than settled.
            throw new TransportException(sprintf('%s %s: %s', $request->method, $request->url, $failure));
        }

        return new Response($status, (string) $body, $headers);
    }

    /**
     * @param array<string, string> $headers filled as the answer's headers arrive
     *
     * @return array<int, mixed>
     */
    private function options(Request $request, array &$headers): array
    {
        $options = [
            CURLOPT_CUSTOMREQUEST => $request->method,
            CURLOPT_URL => $request->url,
            CURLOPT_HTTPHEADER => $this->written($request->headers),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS => (int) round($this->timeout * 1000),
            CURLOPT_HEADERFUNCTION => static function ($handle, string $line) use (&$headers): int {
                $parts = explode(':', $line, 2);

                if (count($parts) === 2) {
                    $headers[strtolower(trim($parts[0]))] = trim($parts[1]);
                }

                return strlen($line);
            },
        ];

        if ($request->body !== null) {
            $options[CURLOPT_POSTFIELDS] = $request->body;
        }

        return $options;
    }

    /**
     * @param array<string, string> $headers
     *
     * @return list<string>
     */
    private function written(array $headers): array
    {
        $written = [];

        foreach ($headers as $name => $value) {
            $written[] = $name . ': ' . $value;
        }

        return $written;
    }
}
