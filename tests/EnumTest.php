<?php

declare(strict_types=1);

namespace Clockster\Tests;

use Clockster\Generated\Enum\DepartmentsInclude;
use Clockster\Generated\Enum\LocationsInclude;
use Clockster\Generated\Enum\UsersRole;
use Clockster\Generated\Enum\UsersStatus;
use Clockster\Generated\Enum\WebhooksEvent;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * The sets of values, and the two rules about how they are named.
 */
#[CoversNothing]
final class EnumTest extends TestCase
{
    public function testAConstantIsTheStringItStandsFor(): void
    {
        // Nothing to unwrap: it goes into a body where the string goes.
        self::assertSame('employee', UsersRole::EMPLOYEE);
        self::assertSame('user.created', WebhooksEvent::USER_CREATED);
    }

    public function testValuesAreEveryOneInTheOrderTheDocumentNamesThem(): void
    {
        self::assertSame(['active', 'dismissed', 'all'], UsersStatus::values());
    }

    public function testOneResourceInsideAnotherSharesTheSet(): void
    {
        // `webhooks` and `webhooks.deliveries` name the same events, and one of them naming a new
        // one would mean both did.
        self::assertContains(WebhooksEvent::TASK_APPROVED, WebhooksEvent::values());
        self::assertFalse(class_exists('Clockster\Generated\Enum\WebhooksDeliveriesEvent'));
    }

    public function testUnrelatedResourcesKeepTheirOwnSet(): void
    {
        // The same single value today, and nothing says the two move together tomorrow.
        self::assertSame(LocationsInclude::values(), DepartmentsInclude::values());
        self::assertNotSame(LocationsInclude::class, DepartmentsInclude::class);
    }
}
