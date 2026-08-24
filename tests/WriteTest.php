<?php

declare(strict_types=1);

namespace Clockster\Tests;

use Clockster\Write;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * The one difference a write turns on: a blank cell is not a value, and null is.
 */
#[CoversNothing]
final class WriteTest extends TestCase
{
    public function testDropsTheKeysHoldingAnEmptyString(): void
    {
        $written = Write::filled(['first_name' => 'Aisulu', 'email' => '', 'phone' => '']);

        self::assertSame(['first_name' => 'Aisulu'], $written);
    }

    public function testKeepsNullBecauseItClears(): void
    {
        $written = Write::filled(['position_id' => null, 'department_id' => '']);

        // The two say different things: one clears the stored position, the other had nothing to
        // say about the department and must leave it alone.
        self::assertSame(['position_id' => null], $written);
    }

    public function testKeepsEverythingElseThatLooksEmpty(): void
    {
        $written = Write::filled(['active' => false, 'break_time' => 0, 'locations' => [], 'title' => '0']);

        self::assertSame(['active' => false, 'break_time' => 0, 'locations' => [], 'title' => '0'], $written);
    }

    public function testWalksIntoNestedRows(): void
    {
        $written = Write::filled(['users' => [
            ['first_name' => 'Aisulu', 'email' => ''],
            ['first_name' => 'Dias', 'email' => 'dias@example.test'],
        ]]);

        self::assertSame(['users' => [
            ['first_name' => 'Aisulu'],
            ['first_name' => 'Dias', 'email' => 'dias@example.test'],
        ]], $written);
    }

    public function testAListKeepsItsNumbering(): void
    {
        $written = Write::filled(['include' => ['location', '', 'department']]);

        // Not [0 => 'location', 2 => 'department'], which would travel as an object rather than a
        // list once it is JSON.
        self::assertSame(['include' => ['location', 'department']], $written);
    }
}
