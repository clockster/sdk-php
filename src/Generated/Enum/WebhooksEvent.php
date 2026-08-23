<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `event` is allowed to be.
 *
 * Sent in a $clockster->webhooks->create() body, a $clockster->webhooks->update() body, a filter on
 * $clockster->webhooks->deliveries->list().
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `WebhooksEvent::USER_CREATED` is `'user.created'`, and a static analyser reads the two as one
 * value. Closed on the way in and only there — an answer naming something this class does not is
 * still a string, and still reaches you.
 */
final class WebhooksEvent
{
    public const USER_CREATED = 'user.created';
    public const USER_UPDATED = 'user.updated';
    public const USER_DELETED = 'user.deleted';
    public const USER_RESTORED = 'user.restored';
    public const USER_PURGED = 'user.purged';
    public const LOCATION_CREATED = 'location.created';
    public const LOCATION_UPDATED = 'location.updated';
    public const LOCATION_DELETED = 'location.deleted';
    public const DEPARTMENT_CREATED = 'department.created';
    public const DEPARTMENT_UPDATED = 'department.updated';
    public const DEPARTMENT_DELETED = 'department.deleted';
    public const POSITION_CREATED = 'position.created';
    public const POSITION_UPDATED = 'position.updated';
    public const POSITION_DELETED = 'position.deleted';
    public const TASK_CREATED = 'task.created';
    public const TASK_COMPLETED = 'task.completed';
    public const TASK_APPROVED = 'task.approved';
    public const TASK_REJECTED = 'task.rejected';
    public const TASK_DELETED = 'task.deleted';

    /** @return list<'user.created'|'user.updated'|'user.deleted'|'user.restored'|'user.purged'|'location.created'|'location.updated'|'location.deleted'|'department.created'|'department.updated'|'department.deleted'|'position.created'|'position.updated'|'position.deleted'|'task.created'|'task.completed'|'task.approved'|'task.rejected'|'task.deleted'> */
    public static function values(): array
    {
        return [
            self::USER_CREATED,
            self::USER_UPDATED,
            self::USER_DELETED,
            self::USER_RESTORED,
            self::USER_PURGED,
            self::LOCATION_CREATED,
            self::LOCATION_UPDATED,
            self::LOCATION_DELETED,
            self::DEPARTMENT_CREATED,
            self::DEPARTMENT_UPDATED,
            self::DEPARTMENT_DELETED,
            self::POSITION_CREATED,
            self::POSITION_UPDATED,
            self::POSITION_DELETED,
            self::TASK_CREATED,
            self::TASK_COMPLETED,
            self::TASK_APPROVED,
            self::TASK_REJECTED,
            self::TASK_DELETED,
        ];
    }
}
