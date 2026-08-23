<?php

declare(strict_types=1);

namespace Clockster\Generated;

/**
 * Every shape the Company API answers with or accepts, as array shapes a static analyser
 * reads.
 *
 * Generated from openapi/company-v3.json — see scripts/generate.php. A key marked optional is absent
 * rather than null: an `include` relation is not there unless it was asked for, and a field
 * left out of a write keeps whatever is stored where a null one clears it.
 *
 * They are documentation and nothing else. What a method answers is a plain array, and a key
 * the API adds tomorrow is in it whether or not this file knows the name.
 *
 * The sets of values come first, written out. Every one of them is on something you send,
 * never in an answer, so writing a field as one closes nothing you read — and the constants
 * for each are a class of their own under Enum.
 *
 * @phpstan-type AttendanceInclude 'user'|'location'|'attachments'
 * @phpstan-type AttendanceSource 'device'|'mobile'|'frontend'|'api'|'system'
 * @phpstan-type AttendanceStatus 'out'|'in'|'break'
 * @phpstan-type DepartmentsInclude 'managers'
 * @phpstan-type DocumentsEmploymentType 'full_time'|'part_time'|'irregular_hours'|'contract_1'|'contract_2'|'apprenticeship'|'traineeship'|'piece_rate'|'probation'|'outstaffing'
 * @phpstan-type DocumentsInclude 'attachments'|'signers'|'labor_contract'
 * @phpstan-type DocumentsParty 'employee'|'counterparty'
 * @phpstan-type DocumentsType 'passport'|'cv'|'diploma'|'medical'|'photo'|'other'|'medical_book'|'employment_agreement'|'termination_of_employment_agreement'|'equipment_agreement'|'application'|'order'|'supplementary_agreement'|'job_description'|'nda'|'non_compete_agreement'|'data_processing_agreement'|'act_of_service_acceptance'|'health_and_safety_briefing'|'shift_schedule'|'letter'|'vacation_schedule'|'contract'|'agreement'|'goods_release_note'|'reconciliation_act'|'return_to_supplier'
 * @phpstan-type LocationsInclude 'managers'
 * @phpstan-type PayrollPayslipsStatus 'draft'|'approved'|'paid'
 * @phpstan-type SchedulesLeaveType 'annual'|'unpaid'|'sick'|'unpaid_sick'|'maternity'|'paternity'|'special'|'day_off'|'compensatory'|'personal'|'emergency'|'unexcused_absence'
 * @phpstan-type SchedulesType 'work'|'free'|'leave'
 * @phpstan-type TasksInclude 'items'|'managers'|'user'|'author'
 * @phpstan-type TasksStatus 'created'|'started'|'paused'|'completed'|'incompleted'|'pastdue'
 * @phpstan-type TimesheetsInclude 'actual'|'variance'|'user'|'location'|'department'|'position'
 * @phpstan-type UserFiltersInclude 'managers'
 * @phpstan-type UserRequestsInclude 'content'|'user'|'author'
 * @phpstan-type UserRequestsStatus 'pending'|'accepted'|'rejected'|'cancelled'|'approval'|'execution'|'signing'
 * @phpstan-type UserRequestsType 'leave'|'work'|'general'|'finance'
 * @phpstan-type UsersEmployment 'full_time'|'part_time'|'irregular_hours'|'contract_1'|'contract_2'|'apprenticeship'|'traineeship'|'piece_rate'|'probation'|'outstaffing'
 * @phpstan-type UsersGender 'male'|'female'|'other'
 * @phpstan-type UsersInclude 'location'|'locations'|'department'|'position'|'user_filters'|'dismissal'|'meta'
 * @phpstan-type UsersLocale 'en'|'ru'|'kk'|'uk'|'id'|'uz'|'az'|'fr'|'vi'|'zh'
 * @phpstan-type UsersRole 'admin'|'employee'
 * @phpstan-type UsersStatus 'active'|'dismissed'|'all'
 * @phpstan-type WebhooksDeliveriesInclude 'payload'
 * @phpstan-type WebhooksEvent 'user.created'|'user.updated'|'user.deleted'|'user.restored'|'user.purged'|'location.created'|'location.updated'|'location.deleted'|'department.created'|'department.updated'|'department.deleted'|'position.created'|'position.updated'|'position.deleted'|'task.created'|'task.completed'|'task.approved'|'task.rejected'|'task.deleted'
 * @phpstan-type MeResponse array{
 *     data: MeData,
 * }
 * @phpstan-type MeData array{
 *     id: int,
 *     title: string,
 * }
 * @phpstan-type AttendanceListResponse array{
 *     data: list<AttendanceListRow>,
 *     links: PageLinks,
 *     meta: PageMeta,
 * }
 * @phpstan-type AttendanceListRow array{
 *     id: int,
 *     user_id: int,
 *     location_id: int|null,
 *     datetime: string,
 *     status: string,
 *     source: string,
 *     latitude: float|null,
 *     longitude: float|null,
 *     address: string|null,
 *     comment: string|null,
 *     user?: EmployeeShort,
 *     location?: AttendanceListRowLocation,
 *     attachments?: list<AttendanceListRowAttachment>,
 * }
 * @phpstan-type EmployeeShort array{
 *     id: int,
 *     external_id: string|null,
 *     code: string|null,
 *     first_name: string,
 *     middle_name: string|null,
 *     last_name: string,
 * }
 * @phpstan-type AttendanceListRowLocation array{
 *     id: int,
 *     external_id: string|null,
 *     code: string|null,
 *     title: string,
 *     description: string|null,
 *     latitude: float|null,
 *     longitude: float|null,
 *     radius: int,
 *     created_at: string,
 *     updated_at: string,
 * }
 * @phpstan-type AttendanceListRowAttachment array{
 *     id: int,
 *     name: string,
 *     description: string|null,
 *     format: string,
 *     url: string,
 *     created_at: string,
 * }
 * @phpstan-type PageLinks array{
 *     first: null,
 *     last: null,
 *     prev: string|null,
 *     next: string|null,
 * }
 * @phpstan-type PageMeta array{
 *     path: string,
 *     per_page: int,
 *     next_cursor: string|null,
 *     prev_cursor: string|null,
 * }
 * @phpstan-type AttendanceRecordBody array{
 *     attendance: list<AttendanceRecordAttendanceItem>,
 * }
 * @phpstan-type AttendanceRecordAttendanceItem array{
 *     user_id: int,
 *     location_id?: int|null,
 *     shift_id?: int|null,
 *     status: AttendanceStatus,
 *     datetime: string,
 *     comment?: string|null,
 * }
 * @phpstan-type AttendanceRecordResponse array{
 *     data: list<AttendanceRecordRow>,
 * }
 * @phpstan-type AttendanceRecordRow array{
 *     user_id: int,
 *     datetime: string,
 *     status: string,
 *     id: int,
 *     result: string,
 * }
 * @phpstan-type DepartmentsDeleteResponse array{
 *     data: DeleteOutcome,
 * }
 * @phpstan-type DeleteOutcome array{
 *     id: int,
 *     result: string,
 * }
 * @phpstan-type DepartmentsGetResponse array{
 *     data: DepartmentsGetData,
 * }
 * @phpstan-type DepartmentsGetData array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 *     description: string|null,
 *     created_at: string,
 *     updated_at: string,
 *     managers?: list<EmployeeShort>,
 * }
 * @phpstan-type DepartmentsListResponse array{
 *     data: list<DepartmentsListRow>,
 *     links: PageLinks,
 *     meta: PageMeta,
 * }
 * @phpstan-type DepartmentsListRow array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 *     description: string|null,
 *     created_at: string,
 *     updated_at: string,
 *     managers?: list<EmployeeShort>,
 * }
 * @phpstan-type DepartmentsUpsertBody array{
 *     items: list<DepartmentsUpsertItem>,
 * }
 * @phpstan-type DepartmentsUpsertItem array{
 *     external_id: string,
 *     title: string,
 *     description?: string|null,
 * }
 * @phpstan-type DepartmentsUpsertResponse array{
 *     data: list<UpsertOutcome>,
 * }
 * @phpstan-type UpsertOutcome array{
 *     external_id: string|null,
 *     id: int,
 *     result: string,
 * }
 * @phpstan-type DocumentsDeleteResponse array{
 *     data: DeleteOutcome,
 * }
 * @phpstan-type DocumentsGetResponse array{
 *     data: DocumentsGetData,
 * }
 * @phpstan-type DocumentsGetData array{
 *     id: int,
 *     external_id: string|null,
 *     user_id: int,
 *     author_id: int,
 *     party: string,
 *     type: string,
 *     name: string,
 *     contract_number: string|null,
 *     employment_type: string|null,
 *     start_date: string|null,
 *     end_date: string|null,
 *     expiration_date: string|null,
 *     parent_document_id: int|null,
 *     signature: DocumentsGetDataSignature,
 *     created_at: string,
 *     updated_at: string,
 *     attachments?: list<DocumentsGetDataAttachment>,
 *     signers?: list<DocumentsGetDataSigner>,
 *     labor_contract?: DocumentsGetDataLaborContract|null,
 * }
 * @phpstan-type DocumentsGetDataSignature array{
 *     state: string,
 *     completed_at: string|null,
 * }
 * @phpstan-type DocumentsGetDataAttachment array{
 *     id: int,
 *     name: string,
 *     description: string|null,
 *     format: string,
 *     url: string,
 *     created_at: string,
 * }
 * @phpstan-type DocumentsGetDataSigner array{
 *     type: string,
 *     status: string,
 *     signed_at: string|null,
 *     signed_via: string|null,
 *     party_id: int,
 *     party_type: string,
 * }
 * @phpstan-type DocumentsGetDataLaborContract array{
 *     id: int,
 *     external_contract_id: string,
 *     iin: string,
 *     contract_number: string|null,
 *     contract_date: string|null,
 *     begin_date: string|null,
 *     end_date: string|null,
 *     termination_date: string|null,
 *     established_post: string,
 *     has_contract_file: bool,
 * }
 * @phpstan-type DocumentsListResponse array{
 *     data: list<DocumentsListRow>,
 *     links: PageLinks,
 *     meta: PageMeta,
 * }
 * @phpstan-type DocumentsListRow array{
 *     id: int,
 *     external_id: string|null,
 *     user_id: int,
 *     author_id: int,
 *     party: string,
 *     type: string,
 *     name: string,
 *     contract_number: string|null,
 *     employment_type: string|null,
 *     start_date: string|null,
 *     end_date: string|null,
 *     expiration_date: string|null,
 *     parent_document_id: int|null,
 *     signature: DocumentsListRowSignature,
 *     created_at: string,
 *     updated_at: string,
 *     attachments?: list<DocumentsListRowAttachment>,
 *     signers?: list<DocumentsListRowSigner>,
 *     labor_contract?: DocumentsListRowLaborContract|null,
 * }
 * @phpstan-type DocumentsListRowSignature array{
 *     state: string,
 *     completed_at: string|null,
 * }
 * @phpstan-type DocumentsListRowAttachment array{
 *     id: int,
 *     name: string,
 *     description: string|null,
 *     format: string,
 *     url: string,
 *     created_at: string,
 * }
 * @phpstan-type DocumentsListRowSigner array{
 *     type: string,
 *     status: string,
 *     signed_at: string|null,
 *     signed_via: string|null,
 *     party_id: int,
 *     party_type: string,
 * }
 * @phpstan-type DocumentsListRowLaborContract array{
 *     id: int,
 *     external_contract_id: string,
 *     iin: string,
 *     contract_number: string|null,
 *     contract_date: string|null,
 *     begin_date: string|null,
 *     end_date: string|null,
 *     termination_date: string|null,
 *     established_post: string,
 *     has_contract_file: bool,
 * }
 * @phpstan-type DocumentsUpsertBody array{
 *     documents: list<DocumentsUpsertDocument>,
 * }
 * @phpstan-type DocumentsUpsertDocument array{
 *     external_id: string,
 *     type: DocumentsType,
 *     user_id: int,
 *     name?: string|null,
 *     contract_number?: string|null,
 *     employment_type?: DocumentsEmploymentType|null,
 *     start_date?: string|null,
 *     end_date?: string|null,
 *     expiration_date?: string|null,
 *     parent_external_id?: string|null,
 *     file_id?: int|null,
 * }
 * @phpstan-type DocumentsUpsertResponse array{
 *     data: list<UpsertOutcome>,
 * }
 * @phpstan-type FilesUploadResponse array{
 *     data: FilesUploadData,
 * }
 * @phpstan-type FilesUploadData array{
 *     id: int,
 *     name: string,
 *     format: string,
 *     url: string,
 *     created_at: string,
 * }
 * @phpstan-type LocationsDeleteResponse array{
 *     data: DeleteOutcome,
 * }
 * @phpstan-type LocationsGetResponse array{
 *     data: LocationsGetData,
 * }
 * @phpstan-type LocationsGetData array{
 *     id: int,
 *     external_id: string|null,
 *     code: string|null,
 *     title: string,
 *     description: string|null,
 *     latitude: float|null,
 *     longitude: float|null,
 *     radius: int,
 *     created_at: string,
 *     updated_at: string,
 *     managers?: list<EmployeeShort>,
 * }
 * @phpstan-type LocationsListResponse array{
 *     data: list<LocationsListRow>,
 *     links: PageLinks,
 *     meta: PageMeta,
 * }
 * @phpstan-type LocationsListRow array{
 *     id: int,
 *     external_id: string|null,
 *     code: string|null,
 *     title: string,
 *     description: string|null,
 *     latitude: float|null,
 *     longitude: float|null,
 *     radius: int,
 *     created_at: string,
 *     updated_at: string,
 *     managers?: list<EmployeeShort>,
 * }
 * @phpstan-type LocationsUpsertBody array{
 *     items: list<LocationsUpsertItem>,
 * }
 * @phpstan-type LocationsUpsertItem array{
 *     external_id: string,
 *     title: string,
 *     description?: string|null,
 *     code?: string|null,
 *     latitude?: float|null,
 *     longitude?: float|null,
 *     radius?: int,
 * }
 * @phpstan-type LocationsUpsertResponse array{
 *     data: list<UpsertOutcome>,
 * }
 * @phpstan-type PayrollPayslipsListResponse array{
 *     data: list<PayrollPayslipsListRow>,
 *     meta: PayrollPayslipsListMeta,
 * }
 * @phpstan-type PayrollPayslipsListRow array{
 *     id: int,
 *     user: PayrollPayslipsListRowUser,
 *     author_id: int,
 *     period: PayrollPayslipsListRowPeriod,
 *     status: string,
 *     currency: string|null,
 *     take_home: float,
 *     ctc: int,
 *     salary: PayrollPayslipsListRowSalary,
 *     additions: list<PayrollPayslipsListRowAddition>,
 *     deductions: list<PayrollPayslipsListRowDeduction>,
 *     allowances: list<PayrollPayslipsListRowAllowance>,
 *     loan_repaid: int,
 *     updated_at: string,
 * }
 * @phpstan-type PayrollPayslipsListRowUser array{
 *     id: int,
 *     external_id: string|null,
 * }
 * @phpstan-type PayrollPayslipsListRowPeriod array{
 *     from: string,
 *     to: string,
 *     month: string,
 * }
 * @phpstan-type PayrollPayslipsListRowSalary array{
 *     basic_rate: float|null,
 *     basic_type: string|null,
 *     work_days: int|null,
 *     worked_days: int|null,
 * }
 * @phpstan-type PayrollPayslipsListRowAddition array{
 *     title: string,
 *     type: string,
 *     value: int,
 *     pre_tax: bool,
 *     comment: string|null,
 * }
 * @phpstan-type PayrollPayslipsListRowDeduction array{
 *     title: string,
 *     type: string,
 *     value: float,
 *     pre_tax: bool,
 *     comment: string|null,
 * }
 * @phpstan-type PayrollPayslipsListRowAllowance array{
 *     title: string,
 *     type: string,
 *     value: int,
 *     pre_tax: bool,
 *     comment: string|null,
 * }
 * @phpstan-type PayrollPayslipsListMeta array{
 *     per_page: int,
 *     next_cursor: string|null,
 *     prev_cursor: string|null,
 * }
 * @phpstan-type PositionsDeleteResponse array{
 *     data: DeleteOutcome,
 * }
 * @phpstan-type PositionsGetResponse array{
 *     data: PositionsGetData,
 * }
 * @phpstan-type PositionsGetData array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 *     description: string|null,
 *     created_at: string,
 *     updated_at: string,
 * }
 * @phpstan-type PositionsListResponse array{
 *     data: list<PositionsListRow>,
 *     links: PageLinks,
 *     meta: PageMeta,
 * }
 * @phpstan-type PositionsListRow array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 *     description: string|null,
 *     created_at: string,
 *     updated_at: string,
 * }
 * @phpstan-type PositionsUpsertBody array{
 *     items: list<PositionsUpsertItem>,
 * }
 * @phpstan-type PositionsUpsertItem array{
 *     external_id: string,
 *     title: string,
 *     description?: string|null,
 * }
 * @phpstan-type PositionsUpsertResponse array{
 *     data: list<UpsertOutcome>,
 * }
 * @phpstan-type SchedulesCreateBody array{
 *     schedules: list<WorkSchedule|FreeSchedule|LeaveSchedule>,
 * }
 * @phpstan-type WorkSchedule array{
 *     type: SchedulesType,
 *     dates: list<string>,
 *     users: list<int>,
 *     location_id?: int|null,
 *     department_id?: int|null,
 *     position_id?: int|null,
 *     timezone: string,
 *     start?: string|null,
 *     end?: string|null,
 *     break_time?: int|null,
 *     grace_start?: int|null,
 *     grace_end?: int|null,
 *     shifts?: list<WorkScheduleShift>|null,
 * }
 * @phpstan-type WorkScheduleShift array{
 *     start: string,
 *     end: string,
 *     location_id?: int|null,
 *     department_id?: int|null,
 *     position_id?: int|null,
 * }
 * @phpstan-type FreeSchedule array{
 *     type: SchedulesType,
 *     dates: list<string>,
 *     users: list<int>,
 *     location_id?: int|null,
 *     department_id?: int|null,
 *     position_id?: int|null,
 *     timezone: string,
 *     start: string,
 *     end: string,
 *     time_planned?: int|null,
 * }
 * @phpstan-type LeaveSchedule array{
 *     type: SchedulesType,
 *     dates: list<string>,
 *     users: list<int>,
 *     location_id?: int|null,
 *     department_id?: int|null,
 *     position_id?: int|null,
 *     leave_type: SchedulesLeaveType,
 * }
 * @phpstan-type SchedulesCreateResponse array{
 *     data: list<SchedulesCreateRow>,
 * }
 * @phpstan-type SchedulesCreateRow array{
 *     id: int,
 *     type: string,
 *     leave_type: string|null,
 *     is_split: bool,
 *     dates: list<string>,
 *     timezone: string|null,
 *     start: string|null,
 *     end: string|null,
 *     time_planned: int,
 *     break_time: int,
 *     grace_start: int,
 *     grace_end: int,
 *     location_id: int|null,
 *     department_id: int|null,
 *     position_id: int|null,
 *     shifts: list<SchedulesCreateRowShift>,
 *     users: list<SchedulesCreateRowUser>,
 * }
 * @phpstan-type SchedulesCreateRowShift array{
 *     id: int,
 *     start: string,
 *     end: string,
 *     time_planned: int,
 *     location_id: int|null,
 *     department_id: int|null,
 *     position_id: int|null,
 * }
 * @phpstan-type SchedulesCreateRowUser array{
 *     id: int,
 *     external_id: string|null,
 * }
 * @phpstan-type SchedulesDeleteResponse array{
 *     data: DeleteOutcome,
 * }
 * @phpstan-type SchedulesGetResponse array{
 *     data: SchedulesGetData,
 * }
 * @phpstan-type SchedulesGetData array{
 *     id: int,
 *     type: string,
 *     leave_type: string|null,
 *     is_split: bool,
 *     dates: list<string>,
 *     timezone: string,
 *     start: string,
 *     end: string,
 *     time_planned: int,
 *     break_time: int,
 *     grace_start: int,
 *     grace_end: int,
 *     location_id: int|null,
 *     department_id: int|null,
 *     position_id: int|null,
 *     shifts: list<SchedulesGetDataShift>,
 *     users: list<SchedulesGetDataUser>,
 * }
 * @phpstan-type SchedulesGetDataShift array{
 *     id: int,
 *     start: string,
 *     end: string,
 *     time_planned: int,
 *     location_id: int|null,
 *     department_id: int|null,
 *     position_id: int|null,
 * }
 * @phpstan-type SchedulesGetDataUser array{
 *     id: int,
 *     external_id: string|null,
 * }
 * @phpstan-type TasksGetResponse array{
 *     data: TasksGetData,
 * }
 * @phpstan-type TasksGetData array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 *     description: string|null,
 *     status: string,
 *     active: bool,
 *     priority: int,
 *     user_id: int,
 *     author_id: int,
 *     category_id: int|null,
 *     location_id: int|null,
 *     department_id: int|null,
 *     position_id: int|null,
 *     due_date: string,
 *     time_start: string,
 *     time_end: string,
 *     timezone: string,
 *     kpi_plan: int,
 *     kpi_fact: int,
 *     time_worked: int,
 *     started_at: string|null,
 *     finished_at: string|null,
 *     created_at: string,
 *     updated_at: string,
 *     items?: list<TasksGetDataItem>,
 *     managers?: list<EmployeeShort>,
 *     user?: EmployeeShort,
 *     author?: EmployeeShort,
 * }
 * @phpstan-type TasksGetDataItem array{
 *     id: int,
 *     title: string,
 *     order: int,
 *     is_completed: bool,
 * }
 * @phpstan-type TasksListResponse array{
 *     data: list<TasksListRow>,
 *     meta: TasksListMeta,
 * }
 * @phpstan-type TasksListRow array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 *     description: string|null,
 *     status: string,
 *     active: bool,
 *     priority: int,
 *     user_id: int,
 *     author_id: int,
 *     category_id: int|null,
 *     location_id: int|null,
 *     department_id: int|null,
 *     position_id: int|null,
 *     due_date: string,
 *     time_start: string,
 *     time_end: string,
 *     timezone: string,
 *     kpi_plan: int,
 *     kpi_fact: int,
 *     time_worked: int,
 *     started_at: string|null,
 *     finished_at: string|null,
 *     created_at: string,
 *     updated_at: string,
 *     items?: list<TasksListRowItem>,
 *     managers?: list<EmployeeShort>,
 *     user?: EmployeeShort,
 *     author?: EmployeeShort,
 * }
 * @phpstan-type TasksListRowItem array{
 *     id: int,
 *     title: string,
 *     order: int,
 *     is_completed: bool,
 * }
 * @phpstan-type TasksListMeta array{
 *     per_page: int,
 *     next_cursor: string|null,
 *     prev_cursor: string|null,
 * }
 * @phpstan-type TasksUpsertBody array{
 *     tasks: list<TasksUpsertTask>,
 * }
 * @phpstan-type TasksUpsertTask array{
 *     external_id: string,
 *     title: string,
 *     description?: string|null,
 *     user_id: int,
 *     category_id?: int|null,
 *     location_id?: int|null,
 *     department_id?: int|null,
 *     position_id?: int|null,
 *     due_date?: string|null,
 *     time_start?: string|null,
 *     time_end?: string|null,
 *     timezone?: string|null,
 *     priority?: 0|1,
 *     active?: bool,
 *     kpi_plan?: float|null,
 *     managers?: list<int>|null,
 *     items?: list<TasksUpsertTaskItem>|null,
 * }
 * @phpstan-type TasksUpsertTaskItem array{
 *     title: string,
 *     order?: int|null,
 * }
 * @phpstan-type TasksUpsertResponse array{
 *     data: list<UpsertOutcome>,
 * }
 * @phpstan-type TimesheetsListResponse array{
 *     data: list<TimesheetsListRow>,
 *     meta: TimesheetsListMeta,
 * }
 * @phpstan-type TimesheetsListRow array{
 *     date: string,
 *     user: TimesheetsListRowUser,
 *     planned: TimesheetsListRowPlanned|null,
 *     actual?: TimesheetsListRowActual,
 *     variance?: TimesheetsListRowVariance,
 * }
 * @phpstan-type TimesheetsListRowUser array{
 *     id: int,
 *     external_id: string|null,
 *     code?: string|null,
 *     first_name?: string,
 *     middle_name?: string|null,
 *     last_name?: string,
 * }
 * @phpstan-type TimesheetsListRowPlanned array{
 *     schedule_id: int,
 *     type: string,
 *     leave_type: string|null,
 *     is_split: bool,
 *     timezone: string,
 *     start: string,
 *     end: string,
 *     time_planned: int,
 *     break_time: int,
 *     grace_start: int,
 *     grace_end: int,
 *     shifts: list<TimesheetsListRowPlannedShift>,
 *     location_id: int|null,
 *     department_id: int|null,
 *     position_id: int|null,
 *     location?: TimesheetsListRowPlannedLocation|null,
 *     department?: TimesheetsListRowPlannedDepartment|null,
 *     position?: TimesheetsListRowPlannedPosition|null,
 * }
 * @phpstan-type TimesheetsListRowPlannedShift array{
 *     id: int,
 *     code: string|null,
 *     start: string,
 *     end: string,
 *     time_planned: int,
 *     location_id: int|null,
 *     department_id: int|null,
 *     position_id: int|null,
 *     location?: TimesheetsListRowPlannedShiftLocation|null,
 *     department?: TimesheetsListRowPlannedShiftDepartment|null,
 *     position?: TimesheetsListRowPlannedShiftPosition|null,
 * }
 * @phpstan-type TimesheetsListRowPlannedShiftLocation array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 * }
 * @phpstan-type TimesheetsListRowPlannedShiftDepartment array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 * }
 * @phpstan-type TimesheetsListRowPlannedShiftPosition array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 * }
 * @phpstan-type TimesheetsListRowPlannedLocation array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 * }
 * @phpstan-type TimesheetsListRowPlannedDepartment array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 * }
 * @phpstan-type TimesheetsListRowPlannedPosition array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 * }
 * @phpstan-type TimesheetsListRowActual array{
 *     in: string|null,
 *     out: string|null,
 *     time_worked: int,
 *     time_break: int,
 *     time_worked_day_off: int,
 *     time_night: int,
 *     shifts: list<TimesheetsListRowActualShift>,
 * }
 * @phpstan-type TimesheetsListRowActualShift array{
 *     id: int,
 *     in: string,
 *     out: string,
 *     time_worked: int,
 * }
 * @phpstan-type TimesheetsListRowVariance array{
 *     time_late: int,
 *     time_early_left: int,
 *     time_overworked: int,
 *     time_underworked: int,
 * }
 * @phpstan-type TimesheetsListMeta array{
 *     users_per_page: int,
 *     next_cursor: string|null,
 *     prev_cursor: string|null,
 * }
 * @phpstan-type UserFiltersDeleteResponse array{
 *     data: DeleteOutcome,
 * }
 * @phpstan-type UserFiltersGetResponse array{
 *     data: UserFiltersGetData,
 * }
 * @phpstan-type UserFiltersGetData array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 *     description: string|null,
 *     created_at: string,
 *     updated_at: string,
 *     managers?: list<EmployeeShort>,
 * }
 * @phpstan-type UserFiltersListResponse array{
 *     data: list<UserFiltersListRow>,
 *     links: PageLinks,
 *     meta: PageMeta,
 * }
 * @phpstan-type UserFiltersListRow array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 *     description: string|null,
 *     created_at: string,
 *     updated_at: string,
 *     managers?: list<EmployeeShort>,
 * }
 * @phpstan-type UserFiltersUpsertBody array{
 *     items: list<UserFiltersUpsertItem>,
 * }
 * @phpstan-type UserFiltersUpsertItem array{
 *     external_id: string,
 *     title: string,
 *     description?: string|null,
 * }
 * @phpstan-type UserFiltersUpsertResponse array{
 *     data: list<UpsertOutcome>,
 * }
 * @phpstan-type UserRequestsGetResponse array{
 *     data: UserRequestsGetData,
 * }
 * @phpstan-type UserRequestsGetData array{
 *     id: string,
 *     type: string,
 *     subtype: string|null,
 *     status: string,
 *     user_id: int,
 *     author_id: int,
 *     period: UserRequestsGetDataPeriod,
 *     comment: string|null,
 *     amount: float|null,
 *     currency: string|null,
 *     created_at: string,
 *     updated_at: string,
 *     content: UserRequestsGetDataContent,
 * }
 * @phpstan-type UserRequestsGetDataPeriod array{
 *     date_start: string,
 *     date_end: string,
 * }
 * @phpstan-type UserRequestsGetDataContent array{
 *     type: string,
 *     clockins: list<UserRequestsGetDataContentClockin>,
 * }
 * @phpstan-type UserRequestsGetDataContentClockin array{
 *     status: string,
 *     datetime: string,
 * }
 * @phpstan-type UserRequestsListResponse array{
 *     data: list<UserRequestsListRow>,
 *     links: PageLinks,
 *     meta: PageMeta,
 * }
 * @phpstan-type UserRequestsListRow array{
 *     id: string,
 *     type: string,
 *     subtype: string|null,
 *     status: string,
 *     user_id: int,
 *     author_id: int,
 *     period: UserRequestsListRowPeriod,
 *     comment: string|null,
 *     amount: float|null,
 *     currency: string|null,
 *     created_at: string,
 *     updated_at: string,
 *     user?: EmployeeShort,
 *     author?: EmployeeShort,
 *     content?: UserRequestsListRowContent,
 * }
 * @phpstan-type UserRequestsListRowPeriod array{
 *     date_start: string|null,
 *     date_end: string|null,
 * }
 * @phpstan-type UserRequestsListRowContent array{
 *     type: string,
 *     clockins?: list<UserRequestsListRowContentClockin>,
 *     amount?: float|null,
 *     currency_id?: int,
 * }
 * @phpstan-type UserRequestsListRowContentClockin array{
 *     status: string,
 *     datetime: string,
 * }
 * @phpstan-type UsersDismissBody array{
 *     users: list<UsersDismissUser>,
 * }
 * @phpstan-type UsersDismissUser array{
 *     external_id?: string,
 *     id?: int,
 * }
 * @phpstan-type UsersDismissResponse array{
 *     data: list<UpsertOutcome>,
 * }
 * @phpstan-type UsersGetResponse array{
 *     data: UsersGetData,
 * }
 * @phpstan-type UsersGetData array{
 *     id: int,
 *     external_id: string|null,
 *     code: string|null,
 *     first_name: string,
 *     middle_name: string|null,
 *     last_name: string,
 *     email: string,
 *     phone: string|null,
 *     extra_phone: string|null,
 *     role: string|null,
 *     gender: string,
 *     national_id: string,
 *     tax_id: string|null,
 *     insurance_id: string|null,
 *     employment: string,
 *     locale: string,
 *     timezone: string,
 *     date_birth: string,
 *     date_hire: string,
 *     date_leave: string|null,
 *     photo: string|null,
 *     created_at: string,
 *     updated_at: string,
 *     dismissed_at: string|null,
 *     location?: UsersGetDataLocation,
 *     locations?: list<UsersGetDataLocation>,
 *     department?: UsersGetDataDepartment,
 *     position?: UsersGetDataPosition,
 *     user_filters?: list<UsersGetDataUserFilter>,
 *     dismissal?: UsersGetDataDismissal,
 *     meta?: UsersGetDataMeta,
 * }
 * @phpstan-type UsersGetDataLocation array{
 *     id: int,
 *     external_id: string|null,
 *     code: string|null,
 *     title: string,
 *     description: string|null,
 *     latitude: float|null,
 *     longitude: float|null,
 *     radius: int,
 *     created_at: string,
 *     updated_at: string,
 * }
 * @phpstan-type UsersGetDataDepartment array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 *     description: string|null,
 *     created_at: string,
 *     updated_at: string,
 * }
 * @phpstan-type UsersGetDataPosition array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 *     description: string|null,
 *     created_at: string,
 *     updated_at: string,
 * }
 * @phpstan-type UsersGetDataUserFilter array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 *     description: string|null,
 *     created_at: string,
 *     updated_at: string,
 * }
 * @phpstan-type UsersGetDataDismissal array{
 *     id: int,
 *     title: string,
 * }
 * @phpstan-type UsersGetDataMeta array{
 *     birth_place: string,
 *     marital_status: string,
 *     religion: string|null,
 *     blood_type: string|null,
 *     children: int|null,
 *     contact_name: string|null,
 *     relationship: string|null,
 *     phone: string|null,
 *     domicile_address: string|null,
 *     domicile_city: string|null,
 *     domicile_province: string|null,
 *     domicile_district: string|null,
 *     domicile_postal_code: string|null,
 *     document_address: string|null,
 *     document_city: string|null,
 *     document_province: string|null,
 *     document_district: string|null,
 *     document_postal_code: string|null,
 *     education_level: string|null,
 *     education_institution: string|null,
 *     education_major: string|null,
 *     graduation_year: int|null,
 *     education_gpa: float|null,
 *     full_name: string|null,
 *     alias_name: string|null,
 *     local_name: string|null,
 *     nationality: string|null,
 *     marriage_date: string|null,
 *     retire_age: int|null,
 *     retire_date: string|null,
 *     ethnic_origin: string|null,
 *     contract_number: string|null,
 *     health_insurance_id: string|null,
 *     gov_savings_id: string|null,
 *     gov_savings_acc: string|null,
 * }
 * @phpstan-type UsersListResponse array{
 *     data: list<UsersListRow>,
 *     links: PageLinks,
 *     meta: PageMeta,
 * }
 * @phpstan-type UsersListRow array{
 *     id: int,
 *     external_id: string|null,
 *     code: string|null,
 *     first_name: string,
 *     middle_name: string|null,
 *     last_name: string,
 *     email: string,
 *     phone: string|null,
 *     extra_phone: string|null,
 *     role: string|null,
 *     gender: string,
 *     national_id: string,
 *     tax_id: string|null,
 *     insurance_id: string|null,
 *     employment: string,
 *     locale: string,
 *     timezone: string,
 *     date_birth: string,
 *     date_hire: string,
 *     date_leave: string|null,
 *     photo: string|null,
 *     created_at: string,
 *     updated_at: string,
 *     dismissed_at: string|null,
 *     dismissal?: UsersListRowDismissal,
 *     location?: UsersListRowLocation,
 *     locations?: list<UsersListRowLocation>,
 *     department?: UsersListRowDepartment,
 *     position?: UsersListRowPosition,
 *     user_filters?: list<UsersListRowUserFilter>,
 *     meta?: UsersListRowMeta,
 * }
 * @phpstan-type UsersListRowDismissal array{
 *     id: int,
 *     title: string,
 * }
 * @phpstan-type UsersListRowLocation array{
 *     id: int,
 *     external_id: string|null,
 *     code: string|null,
 *     title: string,
 *     description: string|null,
 *     latitude: float|null,
 *     longitude: float|null,
 *     radius: int,
 *     created_at: string,
 *     updated_at: string,
 * }
 * @phpstan-type UsersListRowDepartment array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 *     description: string|null,
 *     created_at: string,
 *     updated_at: string,
 * }
 * @phpstan-type UsersListRowPosition array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 *     description: string|null,
 *     created_at: string,
 *     updated_at: string,
 * }
 * @phpstan-type UsersListRowUserFilter array{
 *     id: int,
 *     external_id: string|null,
 *     title: string,
 *     description: string|null,
 *     created_at: string,
 *     updated_at: string,
 * }
 * @phpstan-type UsersListRowMeta array{
 *     birth_place: string,
 *     marital_status: string,
 *     religion: string|null,
 *     blood_type: string|null,
 *     children: int|null,
 *     contact_name: string|null,
 *     relationship: string|null,
 *     phone: string|null,
 *     domicile_address: string|null,
 *     domicile_city: string|null,
 *     domicile_province: string|null,
 *     domicile_district: string|null,
 *     domicile_postal_code: string|null,
 *     document_address: string|null,
 *     document_city: string|null,
 *     document_province: string|null,
 *     document_district: string|null,
 *     document_postal_code: string|null,
 *     education_level: string|null,
 *     education_institution: string|null,
 *     education_major: string|null,
 *     graduation_year: int|null,
 *     education_gpa: float|null,
 *     full_name: string|null,
 *     alias_name: string|null,
 *     local_name: string|null,
 *     nationality: string|null,
 *     marriage_date: string|null,
 *     retire_age: int|null,
 *     retire_date: string|null,
 *     ethnic_origin: string|null,
 *     contract_number: string|null,
 *     health_insurance_id: string|null,
 *     gov_savings_id: string|null,
 *     gov_savings_acc: string|null,
 * }
 * @phpstan-type UsersUpsertBody array{
 *     users: list<UsersUpsertUser>,
 * }
 * @phpstan-type UsersUpsertUser array{
 *     external_id?: string|null,
 *     first_name: string,
 *     middle_name?: string|null,
 *     last_name?: string|null,
 *     code?: string|null,
 *     email?: string|null,
 *     phone?: string|null,
 *     extra_phone?: string|null,
 *     role: UsersRole,
 *     gender?: UsersGender|null,
 *     locale?: UsersLocale|null,
 *     timezone?: string|null,
 *     date_hire?: string|null,
 *     date_leave?: string|null,
 *     date_birth?: string|null,
 *     national_id?: string|null,
 *     tax_id?: string|null,
 *     insurance_id?: string|null,
 *     employment?: UsersEmployment|null,
 *     responsibility?: string|null,
 *     location_id: int,
 *     locations?: list<int>|null,
 *     department_id?: int|null,
 *     position_id?: int|null,
 *     user_filters?: list<int>|null,
 * }
 * @phpstan-type UsersUpsertResponse array{
 *     data: list<UpsertOutcome>,
 * }
 * @phpstan-type WebhooksCreateBody array{
 *     title?: string|null,
 *     url: string,
 *     contact_email?: string|null,
 *     events: list<WebhooksEvent>,
 *     auth_basic?: WebhooksCreateAuthBasic|null,
 *     auth_token?: string|null,
 *     active: bool,
 * }
 * @phpstan-type WebhooksCreateAuthBasic array{
 *     username?: string,
 *     password?: string,
 * }
 * @phpstan-type WebhooksCreateResponse array{
 *     data: WebhooksCreateData,
 * }
 * @phpstan-type WebhooksCreateData array{
 *     id: int,
 *     title: string,
 *     url: string,
 *     contact_email: string,
 *     secret: string,
 *     events: list<string>,
 *     auth: WebhooksCreateDataAuth,
 *     active: bool,
 *     health: WebhooksCreateDataHealth,
 * }
 * @phpstan-type WebhooksCreateDataAuth array{
 *     type: string,
 *     username: string|null,
 * }
 * @phpstan-type WebhooksCreateDataHealth array{
 *     consecutive_failures: int,
 *     last_success_at: string|null,
 *     last_failure_at: string|null,
 *     last_failure_status: int|null,
 *     disabled_at: string|null,
 *     disabled_reason: string|null,
 * }
 * @phpstan-type WebhooksDeleteResponse array{
 *     data: DeleteOutcome,
 * }
 * @phpstan-type WebhooksGetResponse array{
 *     data: WebhooksGetData,
 * }
 * @phpstan-type WebhooksGetData array{
 *     id: int,
 *     title: string,
 *     url: string,
 *     contact_email: string,
 *     secret: string,
 *     events: list<string>,
 *     auth: WebhooksGetDataAuth,
 *     active: bool,
 *     health: WebhooksGetDataHealth,
 * }
 * @phpstan-type WebhooksGetDataAuth array{
 *     type: string,
 *     username: string|null,
 * }
 * @phpstan-type WebhooksGetDataHealth array{
 *     consecutive_failures: int,
 *     last_success_at: string|null,
 *     last_failure_at: string|null,
 *     last_failure_status: int|null,
 *     disabled_at: string|null,
 *     disabled_reason: string|null,
 * }
 * @phpstan-type WebhooksListResponse array{
 *     data: list<WebhooksListRow>,
 *     links: PageLinks,
 *     meta: PageMeta,
 * }
 * @phpstan-type WebhooksListRow array{
 *     id: int,
 *     title: string,
 *     url: string,
 *     contact_email: string,
 *     secret: string,
 *     events: list<string>,
 *     auth: WebhooksListRowAuth,
 *     active: bool,
 *     health: WebhooksListRowHealth,
 * }
 * @phpstan-type WebhooksListRowAuth array{
 *     type: string,
 *     username: string|null,
 * }
 * @phpstan-type WebhooksListRowHealth array{
 *     consecutive_failures: int,
 *     last_success_at: string|null,
 *     last_failure_at: string|null,
 *     last_failure_status: int|null,
 *     disabled_at: string|null,
 *     disabled_reason: string|null,
 * }
 * @phpstan-type WebhooksRotateSecretResponse array{
 *     data: WebhooksRotateSecretData,
 * }
 * @phpstan-type WebhooksRotateSecretData array{
 *     id: int,
 *     title: string,
 *     url: string,
 *     contact_email: string,
 *     secret: string,
 *     events: list<string>,
 *     auth: WebhooksRotateSecretDataAuth,
 *     active: bool,
 *     health: WebhooksRotateSecretDataHealth,
 * }
 * @phpstan-type WebhooksRotateSecretDataAuth array{
 *     type: string,
 *     username: string|null,
 * }
 * @phpstan-type WebhooksRotateSecretDataHealth array{
 *     consecutive_failures: int,
 *     last_success_at: string|null,
 *     last_failure_at: string|null,
 *     last_failure_status: int|null,
 *     disabled_at: string|null,
 *     disabled_reason: string|null,
 * }
 * @phpstan-type WebhooksUpdateBody array{
 *     title?: string|null,
 *     url: string,
 *     contact_email?: string|null,
 *     events: list<WebhooksEvent>,
 *     auth_basic?: WebhooksUpdateAuthBasic|null,
 *     auth_token?: string|null,
 *     active: bool,
 * }
 * @phpstan-type WebhooksUpdateAuthBasic array{
 *     username?: string,
 *     password?: string,
 * }
 * @phpstan-type WebhooksUpdateResponse array{
 *     data: WebhooksUpdateData,
 * }
 * @phpstan-type WebhooksUpdateData array{
 *     id: int,
 *     title: string,
 *     url: string,
 *     contact_email: string,
 *     secret: string,
 *     events: list<string>,
 *     auth: WebhooksUpdateDataAuth,
 *     active: bool,
 *     health: WebhooksUpdateDataHealth,
 * }
 * @phpstan-type WebhooksUpdateDataAuth array{
 *     type: string,
 *     username: string|null,
 * }
 * @phpstan-type WebhooksUpdateDataHealth array{
 *     consecutive_failures: int,
 *     last_success_at: string|null,
 *     last_failure_at: string|null,
 *     last_failure_status: int|null,
 *     disabled_at: string|null,
 *     disabled_reason: string|null,
 * }
 * @phpstan-type WebhooksDeliveriesGetResponse array{
 *     data: WebhooksDeliveriesGetData,
 * }
 * @phpstan-type WebhooksDeliveriesGetData array{
 *     id: int,
 *     webhook_id: int,
 *     event: string,
 *     state: string,
 *     attempts: int,
 *     response_status: int,
 *     failure_reason: string|null,
 *     occurred_at: string,
 *     next_attempt_at: string|null,
 *     processed_at: string,
 *     payload: WebhooksDeliveriesGetDataPayload,
 * }
 * @phpstan-type WebhooksDeliveriesGetDataPayload array{
 *     id: int,
 * }
 * @phpstan-type WebhooksDeliveriesListResponse array{
 *     data: list<WebhooksDeliveriesListRow>,
 *     links: PageLinks,
 *     meta: PageMeta,
 * }
 * @phpstan-type WebhooksDeliveriesListRow array{
 *     id: int,
 *     webhook_id: int,
 *     event: string,
 *     state: string,
 *     attempts: int,
 *     response_status: int,
 *     failure_reason: string|null,
 *     occurred_at: string,
 *     next_attempt_at: string|null,
 *     processed_at: string|null,
 *     payload?: WebhooksDeliveriesListRowPayload,
 * }
 * @phpstan-type WebhooksDeliveriesListRowPayload array{
 *     id: int,
 * }
 * @phpstan-type WebhooksDeliveriesRedeliverResponse array{
 *     data: DeleteOutcome,
 * }
 * @phpstan-type WebhooksEventsListResponse array{
 *     data: list<string>,
 * }
 */
final class Shapes
{
}
