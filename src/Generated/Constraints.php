<?php

declare(strict_types=1);

namespace Clockster\Generated;

/**
 * What the Company API says each body must be, in the little of the document Validator checks:
 * which fields a body names, which of them it insists on, what type each takes, the sets and the
 * bounds and the two date formats.
 *
 * Generated from openapi/company-v3.json — see scripts/generate.php. Read only where a client was
 * built with `validate: true`, and not otherwise: a body reaches the API as it was handed over, and
 * this is a courtesy on the way rather than a gate. Keyed by the method and the path as the
 * document writes them, so a path holding an id is matched rather than looked up.
 */
final class Constraints
{
    /** @var array<string, array<string, mixed>> */
    public const BODIES = [
        'POST /company/v3/attendance' => [
            'type' => 'object',
            'required' => ['attendance'],
            'properties' => [
                'attendance' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 100,
                    'items' => [
                        'type' => 'object',
                        'required' => ['user_id', 'status', 'datetime'],
                        'properties' => [
                            'user_id' => ['type' => 'integer'],
                            'location_id' => ['type' => 'integer', 'null' => true],
                            'shift_id' => ['type' => 'integer', 'null' => true],
                            'status' => ['type' => 'string', 'enum' => ['out', 'in', 'break']],
                            'datetime' => ['type' => 'string', 'format' => 'date-time'],
                            'comment' => ['type' => 'string', 'null' => true, 'maxLength' => 250],
                        ],
                    ],
                ],
            ],
        ],
        'POST /company/v3/departments/upsert' => [
            'type' => 'object',
            'required' => ['items'],
            'properties' => [
                'items' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 100,
                    'items' => [
                        'type' => 'object',
                        'required' => ['external_id', 'title'],
                        'properties' => [
                            'external_id' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 64],
                            'title' => ['type' => 'string', 'maxLength' => 200],
                            'description' => ['type' => 'string', 'null' => true, 'maxLength' => 500],
                        ],
                    ],
                ],
            ],
        ],
        'POST /company/v3/documents/upsert' => [
            'type' => 'object',
            'required' => ['documents'],
            'properties' => [
                'documents' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 100,
                    'items' => [
                        'type' => 'object',
                        'required' => ['external_id', 'type', 'user_id'],
                        'properties' => [
                            'external_id' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 64],
                            'type' => [
                                'type' => 'string',
                                'enum' => [
                                    'passport',
                                    'cv',
                                    'diploma',
                                    'medical',
                                    'photo',
                                    'other',
                                    'medical_book',
                                    'employment_agreement',
                                    'termination_of_employment_agreement',
                                    'equipment_agreement',
                                    'application',
                                    'order',
                                    'supplementary_agreement',
                                    'job_description',
                                    'nda',
                                    'non_compete_agreement',
                                    'data_processing_agreement',
                                    'act_of_service_acceptance',
                                    'health_and_safety_briefing',
                                    'shift_schedule',
                                    'letter',
                                    'vacation_schedule',
                                    'contract',
                                    'agreement',
                                    'goods_release_note',
                                    'reconciliation_act',
                                    'return_to_supplier',
                                    'driver_license',
                                    'birth_certificate',
                                    'marriage_certificate',
                                    'divorce_certificate',
                                    'change_fio_certificate',
                                ],
                            ],
                            'user_id' => ['type' => 'integer'],
                            'name' => ['type' => 'string', 'null' => true, 'maxLength' => 255],
                            'contract_number' => ['type' => 'string', 'null' => true, 'maxLength' => 255],
                            'employment_type' => [
                                'type' => 'string',
                                'null' => true,
                                'enum' => [
                                    'full_time',
                                    'part_time',
                                    'irregular_hours',
                                    'contract_1',
                                    'contract_2',
                                    'apprenticeship',
                                    'traineeship',
                                    'piece_rate',
                                    'probation',
                                    'outstaffing',
                                ],
                            ],
                            'start_date' => ['type' => 'string', 'null' => true, 'format' => 'date'],
                            'end_date' => ['type' => 'string', 'null' => true, 'format' => 'date'],
                            'expiration_date' => ['type' => 'string', 'null' => true, 'format' => 'date'],
                            'parent_external_id' => [
                                'type' => 'string',
                                'null' => true,
                                'minLength' => 1,
                                'maxLength' => 64,
                            ],
                            'file_id' => ['type' => 'integer', 'null' => true],
                        ],
                    ],
                ],
            ],
        ],
        'POST /company/v3/locations/upsert' => [
            'type' => 'object',
            'required' => ['items'],
            'properties' => [
                'items' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 100,
                    'items' => [
                        'type' => 'object',
                        'required' => ['external_id', 'title'],
                        'properties' => [
                            'external_id' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 64],
                            'title' => ['type' => 'string', 'maxLength' => 200],
                            'description' => ['type' => 'string', 'null' => true, 'maxLength' => 500],
                            'code' => ['type' => 'string', 'null' => true, 'maxLength' => 50],
                            'latitude' => [
                                'type' => 'number',
                                'null' => true,
                                'minimum' => -90,
                                'maximum' => 90,
                            ],
                            'longitude' => [
                                'type' => 'number',
                                'null' => true,
                                'minimum' => -180,
                                'maximum' => 180,
                            ],
                            'radius' => ['type' => 'integer', 'minimum' => 50, 'maximum' => 700],
                        ],
                    ],
                ],
            ],
        ],
        'POST /company/v3/payroll/single-adjustments' => [
            'type' => 'object',
            'required' => ['adjustments'],
            'properties' => [
                'adjustments' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 100,
                    'items' => [
                        'type' => 'object',
                        'required' => ['user_id', 'type', 'amount', 'date'],
                        'properties' => [
                            'user_id' => ['type' => 'integer'],
                            'type' => [
                                'type' => 'string',
                                'enum' => [
                                    'service_charge',
                                    'single_addition_pre_tax',
                                    'single_addition_post_tax',
                                    'single_loan',
                                    'single_deduction_pre_tax',
                                    'single_deduction_post_tax',
                                ],
                            ],
                            'amount' => ['type' => 'number', 'minimum' => 0, 'maximum' => 999999999],
                            'date' => ['type' => 'string', 'format' => 'date'],
                            'title' => ['type' => 'string', 'null' => true, 'maxLength' => 250],
                        ],
                    ],
                ],
            ],
        ],
        'POST /company/v3/positions/upsert' => [
            'type' => 'object',
            'required' => ['items'],
            'properties' => [
                'items' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 100,
                    'items' => [
                        'type' => 'object',
                        'required' => ['external_id', 'title'],
                        'properties' => [
                            'external_id' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 64],
                            'title' => ['type' => 'string', 'maxLength' => 200],
                            'description' => ['type' => 'string', 'null' => true, 'maxLength' => 500],
                        ],
                    ],
                ],
            ],
        ],
        'POST /company/v3/schedules' => [
            'type' => 'object',
            'required' => ['schedules'],
            'properties' => [
                'schedules' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 25,
                    'items' => [
                        'oneOf' => [
                            'work' => [
                                'type' => 'object',
                                'required' => ['type', 'dates', 'users', 'timezone'],
                                'properties' => [
                                    'type' => ['type' => 'string', 'enum' => ['work', 'free', 'leave']],
                                    'dates' => [
                                        'type' => 'array',
                                        'minItems' => 1,
                                        'maxItems' => 366,
                                        'items' => ['type' => 'string', 'format' => 'date'],
                                    ],
                                    'users' => [
                                        'type' => 'array',
                                        'minItems' => 1,
                                        'maxItems' => 200,
                                        'items' => ['type' => 'integer'],
                                    ],
                                    'location_id' => ['type' => 'integer', 'null' => true],
                                    'department_id' => ['type' => 'integer', 'null' => true],
                                    'position_id' => ['type' => 'integer', 'null' => true],
                                    'timezone' => [
                                        'type' => 'string',
                                        'pattern' => '^(?:Z|[+-](?:2[0-3]|[01][0-9]):[0-5][0-9])$',
                                    ],
                                    'start' => [
                                        'type' => 'string',
                                        'null' => true,
                                        'format' => 'date-time',
                                    ],
                                    'end' => [
                                        'type' => 'string',
                                        'null' => true,
                                        'format' => 'date-time',
                                    ],
                                    'break_time' => [
                                        'type' => 'integer',
                                        'null' => true,
                                        'minimum' => 0,
                                        'maximum' => 43200,
                                    ],
                                    'grace_start' => [
                                        'type' => 'integer',
                                        'null' => true,
                                        'minimum' => 0,
                                        'maximum' => 3600,
                                    ],
                                    'grace_end' => [
                                        'type' => 'integer',
                                        'null' => true,
                                        'minimum' => 0,
                                        'maximum' => 3600,
                                    ],
                                    'shifts' => [
                                        'type' => 'array',
                                        'null' => true,
                                        'minItems' => 1,
                                        'maxItems' => 8,
                                        'items' => [
                                            'type' => 'object',
                                            'required' => ['start', 'end'],
                                            'properties' => [
                                                'start' => ['type' => 'string', 'format' => 'date-time'],
                                                'end' => ['type' => 'string', 'format' => 'date-time'],
                                                'location_id' => ['type' => 'integer', 'null' => true],
                                                'department_id' => ['type' => 'integer', 'null' => true],
                                                'position_id' => ['type' => 'integer', 'null' => true],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'free' => [
                                'type' => 'object',
                                'required' => ['type', 'dates', 'users', 'timezone', 'start', 'end'],
                                'properties' => [
                                    'type' => ['type' => 'string', 'enum' => ['work', 'free', 'leave']],
                                    'dates' => [
                                        'type' => 'array',
                                        'minItems' => 1,
                                        'maxItems' => 366,
                                        'items' => ['type' => 'string', 'format' => 'date'],
                                    ],
                                    'users' => [
                                        'type' => 'array',
                                        'minItems' => 1,
                                        'maxItems' => 200,
                                        'items' => ['type' => 'integer'],
                                    ],
                                    'location_id' => ['type' => 'integer', 'null' => true],
                                    'department_id' => ['type' => 'integer', 'null' => true],
                                    'position_id' => ['type' => 'integer', 'null' => true],
                                    'timezone' => [
                                        'type' => 'string',
                                        'pattern' => '^(?:Z|[+-](?:2[0-3]|[01][0-9]):[0-5][0-9])$',
                                    ],
                                    'start' => ['type' => 'string', 'format' => 'date-time'],
                                    'end' => ['type' => 'string', 'format' => 'date-time'],
                                    'time_planned' => [
                                        'type' => 'integer',
                                        'null' => true,
                                        'minimum' => 0,
                                        'maximum' => 86400,
                                    ],
                                ],
                            ],
                            'leave' => [
                                'type' => 'object',
                                'required' => ['type', 'dates', 'users', 'leave_type'],
                                'properties' => [
                                    'type' => ['type' => 'string', 'enum' => ['work', 'free', 'leave']],
                                    'dates' => [
                                        'type' => 'array',
                                        'minItems' => 1,
                                        'maxItems' => 366,
                                        'items' => ['type' => 'string', 'format' => 'date'],
                                    ],
                                    'users' => [
                                        'type' => 'array',
                                        'minItems' => 1,
                                        'maxItems' => 200,
                                        'items' => ['type' => 'integer'],
                                    ],
                                    'location_id' => ['type' => 'integer', 'null' => true],
                                    'department_id' => ['type' => 'integer', 'null' => true],
                                    'position_id' => ['type' => 'integer', 'null' => true],
                                    'leave_type' => [
                                        'type' => 'string',
                                        'enum' => [
                                            'annual',
                                            'unpaid',
                                            'sick',
                                            'unpaid_sick',
                                            'maternity',
                                            'paternity',
                                            'special',
                                            'day_off',
                                            'compensatory',
                                            'personal',
                                            'emergency',
                                            'unexcused_absence',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'on' => 'type',
                    ],
                ],
            ],
        ],
        'POST /company/v3/tasks/upsert' => [
            'type' => 'object',
            'required' => ['tasks'],
            'properties' => [
                'tasks' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 100,
                    'items' => [
                        'type' => 'object',
                        'required' => ['external_id', 'title', 'user_id'],
                        'properties' => [
                            'external_id' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 64],
                            'title' => ['type' => 'string', 'maxLength' => 255],
                            'description' => ['type' => 'string', 'null' => true, 'maxLength' => 1000],
                            'user_id' => ['type' => 'integer'],
                            'category_id' => ['type' => 'integer', 'null' => true],
                            'location_id' => ['type' => 'integer', 'null' => true],
                            'department_id' => ['type' => 'integer', 'null' => true],
                            'position_id' => ['type' => 'integer', 'null' => true],
                            'due_date' => ['type' => 'string', 'null' => true, 'format' => 'date'],
                            'time_start' => ['type' => 'string', 'null' => true, 'format' => 'date-time'],
                            'time_end' => ['type' => 'string', 'null' => true, 'format' => 'date-time'],
                            'timezone' => [
                                'type' => 'string',
                                'null' => true,
                                'pattern' => '^(?:Z|[+-](?:2[0-3]|[01][0-9]):[0-5][0-9])$',
                            ],
                            'priority' => ['type' => 'integer', 'enum' => [0, 1]],
                            'active' => ['type' => 'boolean'],
                            'kpi_plan' => ['type' => 'number', 'null' => true, 'minimum' => 0],
                            'managers' => [
                                'type' => 'array',
                                'null' => true,
                                'maxItems' => 10,
                                'items' => ['type' => 'integer'],
                            ],
                            'items' => [
                                'type' => 'array',
                                'null' => true,
                                'maxItems' => 200,
                                'items' => [
                                    'type' => 'object',
                                    'required' => ['title'],
                                    'properties' => [
                                        'title' => ['type' => 'string', 'maxLength' => 500],
                                        'order' => ['type' => 'integer', 'null' => true, 'minimum' => 0],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'POST /company/v3/user-filters/upsert' => [
            'type' => 'object',
            'required' => ['items'],
            'properties' => [
                'items' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 100,
                    'items' => [
                        'type' => 'object',
                        'required' => ['external_id', 'title'],
                        'properties' => [
                            'external_id' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 64],
                            'title' => ['type' => 'string', 'maxLength' => 200],
                            'description' => ['type' => 'string', 'null' => true, 'maxLength' => 500],
                        ],
                    ],
                ],
            ],
        ],
        'POST /company/v3/users/dismiss' => [
            'type' => 'object',
            'required' => ['users'],
            'properties' => [
                'users' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 100,
                    'items' => [
                        'type' => 'object',
                        'required' => [],
                        'properties' => [
                            'external_id' => ['type' => 'string', 'maxLength' => 64],
                            'id' => ['type' => 'integer'],
                        ],
                    ],
                ],
            ],
        ],
        'POST /company/v3/users/upsert' => [
            'type' => 'object',
            'required' => ['users'],
            'properties' => [
                'users' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 100,
                    'items' => [
                        'type' => 'object',
                        'required' => ['first_name', 'role', 'location_id'],
                        'properties' => [
                            'external_id' => [
                                'type' => 'string',
                                'null' => true,
                                'minLength' => 1,
                                'maxLength' => 64,
                            ],
                            'first_name' => ['type' => 'string', 'maxLength' => 20],
                            'middle_name' => ['type' => 'string', 'null' => true, 'maxLength' => 40],
                            'last_name' => ['type' => 'string', 'null' => true, 'maxLength' => 40],
                            'code' => [
                                'type' => 'string',
                                'null' => true,
                                'minLength' => 1,
                                'maxLength' => 40,
                            ],
                            'email' => ['type' => 'string', 'null' => true, 'maxLength' => 255],
                            'phone' => [
                                'type' => 'string',
                                'null' => true,
                                'minLength' => 10,
                                'maxLength' => 21,
                            ],
                            'extra_phone' => [
                                'type' => 'string',
                                'null' => true,
                                'minLength' => 10,
                                'maxLength' => 21,
                            ],
                            'role' => ['type' => 'string', 'enum' => ['admin', 'employee']],
                            'gender' => [
                                'type' => 'string',
                                'null' => true,
                                'enum' => ['male', 'female', 'other'],
                            ],
                            'locale' => [
                                'type' => 'string',
                                'null' => true,
                                'enum' => ['en', 'ru', 'kk', 'uk', 'id', 'uz', 'az', 'fr', 'vi', 'zh'],
                            ],
                            'timezone' => ['type' => 'string', 'null' => true, 'maxLength' => 64],
                            'date_hire' => ['type' => 'string', 'null' => true, 'format' => 'date'],
                            'date_leave' => ['type' => 'string', 'null' => true, 'format' => 'date'],
                            'date_birth' => ['type' => 'string', 'null' => true, 'format' => 'date'],
                            'national_id' => ['type' => 'string', 'null' => true, 'maxLength' => 20],
                            'tax_id' => ['type' => 'string', 'null' => true, 'maxLength' => 20],
                            'insurance_id' => ['type' => 'string', 'null' => true, 'maxLength' => 20],
                            'employment' => [
                                'type' => 'string',
                                'null' => true,
                                'enum' => [
                                    'full_time',
                                    'part_time',
                                    'irregular_hours',
                                    'contract_1',
                                    'contract_2',
                                    'apprenticeship',
                                    'traineeship',
                                    'piece_rate',
                                    'probation',
                                    'outstaffing',
                                ],
                            ],
                            'responsibility' => ['type' => 'string', 'null' => true, 'maxLength' => 5000],
                            'location_id' => ['type' => 'integer'],
                            'locations' => [
                                'type' => 'array',
                                'null' => true,
                                'maxItems' => 50,
                                'items' => ['type' => 'integer'],
                            ],
                            'department_id' => ['type' => 'integer', 'null' => true],
                            'position_id' => ['type' => 'integer', 'null' => true],
                            'user_filters' => [
                                'type' => 'array',
                                'null' => true,
                                'maxItems' => 50,
                                'items' => ['type' => 'integer'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'POST /company/v3/webhooks' => [
            'type' => 'object',
            'required' => ['url', 'events', 'active'],
            'properties' => [
                'title' => ['type' => 'string', 'null' => true, 'maxLength' => 255],
                'url' => ['type' => 'string', 'maxLength' => 2048],
                'contact_email' => ['type' => 'string', 'null' => true, 'maxLength' => 255],
                'events' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 50,
                    'items' => [
                        'type' => 'string',
                        'enum' => [
                            'user.created',
                            'user.updated',
                            'user.deleted',
                            'user.restored',
                            'user.purged',
                            'location.created',
                            'location.updated',
                            'location.deleted',
                            'department.created',
                            'department.updated',
                            'department.deleted',
                            'position.created',
                            'position.updated',
                            'position.deleted',
                            'task.created',
                            'task.completed',
                            'task.approved',
                            'task.rejected',
                            'task.deleted',
                        ],
                    ],
                ],
                'auth_basic' => [
                    'type' => 'object',
                    'null' => true,
                    'required' => [],
                    'properties' => [
                        'username' => ['type' => 'string', 'maxLength' => 255],
                        'password' => ['type' => 'string', 'maxLength' => 255],
                    ],
                ],
                'auth_token' => ['type' => 'string', 'null' => true, 'maxLength' => 2048],
                'active' => ['type' => 'boolean'],
            ],
        ],
        'PUT /company/v3/webhooks/{id}' => [
            'type' => 'object',
            'required' => ['url', 'events', 'active'],
            'properties' => [
                'title' => ['type' => 'string', 'null' => true, 'maxLength' => 255],
                'url' => ['type' => 'string', 'maxLength' => 2048],
                'contact_email' => ['type' => 'string', 'null' => true, 'maxLength' => 255],
                'events' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 50,
                    'items' => [
                        'type' => 'string',
                        'enum' => [
                            'user.created',
                            'user.updated',
                            'user.deleted',
                            'user.restored',
                            'user.purged',
                            'location.created',
                            'location.updated',
                            'location.deleted',
                            'department.created',
                            'department.updated',
                            'department.deleted',
                            'position.created',
                            'position.updated',
                            'position.deleted',
                            'task.created',
                            'task.completed',
                            'task.approved',
                            'task.rejected',
                            'task.deleted',
                        ],
                    ],
                ],
                'auth_basic' => [
                    'type' => 'object',
                    'null' => true,
                    'required' => [],
                    'properties' => [
                        'username' => ['type' => 'string', 'maxLength' => 255],
                        'password' => ['type' => 'string', 'maxLength' => 255],
                    ],
                ],
                'auth_token' => ['type' => 'string', 'null' => true, 'maxLength' => 2048],
                'active' => ['type' => 'boolean'],
            ],
        ],
    ];
}
