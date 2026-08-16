<?php

declare(strict_types=1);

namespace Clockster\Tests;

use Clockster\Client;
use Clockster\Exception\ServerException;
use Clockster\Http\Response;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * Walking a listing, against a stub listing rather than the API.
 */
#[CoversNothing]
final class PaginationTest extends TestCase
{
    /**
     * A page of rows, with the cursor that leads to the next one.
     *
     * @param list<int> $ids
     */
    private function page(array $ids, ?string $next): Response
    {
        $rows = array_map(static fn (int $id): array => ['id' => $id, 'first_name' => 'row'], $ids);

        return new Response(200, (string) json_encode([
            'data' => $rows,
            'links' => ['first' => null, 'last' => null, 'prev' => null, 'next' => null],
            'meta' => [
                'path' => '/users',
                'per_page' => 50,
                'next_cursor' => $next,
                'prev_cursor' => null,
            ],
        ]));
    }

    public function testWalksEveryRowAcrossEveryPage(): void
    {
        $transport = new RecordingTransport(
            $this->page([1, 2], '1'),
            $this->page([3], '2'),
            $this->page([4, 5], null),
        );

        $found = [];

        foreach ((new Client('token', transport: $transport))->users->listAll() as $row) {
            $found[] = $row['id'];
        }

        self::assertSame([1, 2, 3, 4, 5], $found);

        // The first page carries no cursor, and every page after it carries the one it was given.
        self::assertArrayNotHasKey('cursor', $transport->query(0));
        self::assertSame('1', $transport->query(1)['cursor']);
        self::assertSame('2', $transport->query(2)['cursor']);
    }

    public function testPassesTheFiltersThroughUnchanged(): void
    {
        $transport = new RecordingTransport($this->page([1], '1'), $this->page([2], null));

        $walk = (new Client('token', transport: $transport))->users->listAll(perPage: 100, include: ['location']);

        iterator_to_array($walk, false);

        foreach ([0, 1] as $index) {
            self::assertSame('100', $transport->query($index)['per_page']);
            self::assertSame('location', $transport->query($index)['include']);
        }
    }

    public function testStopsOnACursorThatRepeats(): void
    {
        // A cursor that repeats would page until the process is killed.
        $transport = new RecordingTransport($this->page([1], 'same'), $this->page([2], 'same'));

        $rows = iterator_to_array((new Client('token', transport: $transport))->users->listAll(), false);

        self::assertCount(2, $rows);
        self::assertCount(2, $transport->seen);
    }

    public function testARefusedPageIsThrownWhereItWasRefused(): void
    {
        $transport = new RecordingTransport(
            $this->page([1], '1'),
            new Response(503, '{"error":{"code":"unavailable","message":"Down.","request_id":"1"}}'),
        );

        $walk = (new Client('token', transport: $transport))->users->listAll();
        $found = [];

        $this->expectException(ServerException::class);

        foreach ($walk as $row) {
            $found[] = $row['id'];
        }
    }

    public function testLeavingTheLoopStopsTheWalk(): void
    {
        $transport = new RecordingTransport($this->page([1, 2], '1'), $this->page([3], null));

        foreach ((new Client('token', transport: $transport))->users->listAll() as $row) {
            break;
        }

        // A Generator asks for the next page only when the loop asks for the next row.
        self::assertCount(1, $transport->seen);
    }
}
