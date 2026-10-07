<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `type` is allowed to be.
 *
 * Sent in a `$clockster->documents->upsert()` body, a filter on `$clockster->documents->list()`.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `DocumentsType::PASSPORT` is `'passport'`, and a static analyser reads the two as one value.
 * Closed on the way in and only there — an answer naming something this class does not is still a
 * string, and still reaches you.
 */
final class DocumentsType
{
    public const PASSPORT = 'passport';
    public const CV = 'cv';
    public const DIPLOMA = 'diploma';
    public const MEDICAL = 'medical';
    public const PHOTO = 'photo';
    public const OTHER = 'other';
    public const MEDICAL_BOOK = 'medical_book';
    public const EMPLOYMENT_AGREEMENT = 'employment_agreement';
    public const TERMINATION_OF_EMPLOYMENT_AGREEMENT = 'termination_of_employment_agreement';
    public const EQUIPMENT_AGREEMENT = 'equipment_agreement';
    public const APPLICATION = 'application';
    public const ORDER = 'order';
    public const SUPPLEMENTARY_AGREEMENT = 'supplementary_agreement';
    public const JOB_DESCRIPTION = 'job_description';
    public const NDA = 'nda';
    public const NON_COMPETE_AGREEMENT = 'non_compete_agreement';
    public const DATA_PROCESSING_AGREEMENT = 'data_processing_agreement';
    public const ACT_OF_SERVICE_ACCEPTANCE = 'act_of_service_acceptance';
    public const HEALTH_AND_SAFETY_BRIEFING = 'health_and_safety_briefing';
    public const SHIFT_SCHEDULE = 'shift_schedule';
    public const LETTER = 'letter';
    public const VACATION_SCHEDULE = 'vacation_schedule';
    public const CONTRACT = 'contract';
    public const AGREEMENT = 'agreement';
    public const GOODS_RELEASE_NOTE = 'goods_release_note';
    public const RECONCILIATION_ACT = 'reconciliation_act';
    public const RETURN_TO_SUPPLIER = 'return_to_supplier';
    public const DRIVER_LICENSE = 'driver_license';
    public const BIRTH_CERTIFICATE = 'birth_certificate';
    public const MARRIAGE_CERTIFICATE = 'marriage_certificate';
    public const DIVORCE_CERTIFICATE = 'divorce_certificate';
    public const CHANGE_FIO_CERTIFICATE = 'change_fio_certificate';
    public const SRTS = 'srts';
    public const VACCINATION = 'vaccination';
    public const SOCIAL_ID = 'social_id';
    public const DISABILITY_CERTIFICATE = 'disability_certificate';
    public const LARGE_FAMILY_CERTIFICATE = 'large_family_certificate';
    public const ASP_CERTIFICATE = 'asp_certificate';
    public const TECH_PASSPORT = 'tech_passport';
    public const PENSION = 'pension';
    public const RK_PASSPORT = 'rk_passport';
    public const STUDENT_CARD = 'student_card';
    public const VNZH = 'vnzh';
    public const PCR_CERTIFICATE = 'pcr_certificate';
    public const LBG_CARD = 'lbg_card';
    public const INSURANCE_POLICY = 'insurance_policy';
    public const HUNTER = 'hunter';
    public const ORALMAN = 'oralman';
    public const ATTORNEY = 'attorney';

    /** @return list<'passport'|'cv'|'diploma'|'medical'|'photo'|'other'|'medical_book'|'employment_agreement'|'termination_of_employment_agreement'|'equipment_agreement'|'application'|'order'|'supplementary_agreement'|'job_description'|'nda'|'non_compete_agreement'|'data_processing_agreement'|'act_of_service_acceptance'|'health_and_safety_briefing'|'shift_schedule'|'letter'|'vacation_schedule'|'contract'|'agreement'|'goods_release_note'|'reconciliation_act'|'return_to_supplier'|'driver_license'|'birth_certificate'|'marriage_certificate'|'divorce_certificate'|'change_fio_certificate'|'srts'|'vaccination'|'social_id'|'disability_certificate'|'large_family_certificate'|'asp_certificate'|'tech_passport'|'pension'|'rk_passport'|'student_card'|'vnzh'|'pcr_certificate'|'lbg_card'|'insurance_policy'|'hunter'|'oralman'|'attorney'> */
    public static function values(): array
    {
        return [
            self::PASSPORT,
            self::CV,
            self::DIPLOMA,
            self::MEDICAL,
            self::PHOTO,
            self::OTHER,
            self::MEDICAL_BOOK,
            self::EMPLOYMENT_AGREEMENT,
            self::TERMINATION_OF_EMPLOYMENT_AGREEMENT,
            self::EQUIPMENT_AGREEMENT,
            self::APPLICATION,
            self::ORDER,
            self::SUPPLEMENTARY_AGREEMENT,
            self::JOB_DESCRIPTION,
            self::NDA,
            self::NON_COMPETE_AGREEMENT,
            self::DATA_PROCESSING_AGREEMENT,
            self::ACT_OF_SERVICE_ACCEPTANCE,
            self::HEALTH_AND_SAFETY_BRIEFING,
            self::SHIFT_SCHEDULE,
            self::LETTER,
            self::VACATION_SCHEDULE,
            self::CONTRACT,
            self::AGREEMENT,
            self::GOODS_RELEASE_NOTE,
            self::RECONCILIATION_ACT,
            self::RETURN_TO_SUPPLIER,
            self::DRIVER_LICENSE,
            self::BIRTH_CERTIFICATE,
            self::MARRIAGE_CERTIFICATE,
            self::DIVORCE_CERTIFICATE,
            self::CHANGE_FIO_CERTIFICATE,
            self::SRTS,
            self::VACCINATION,
            self::SOCIAL_ID,
            self::DISABILITY_CERTIFICATE,
            self::LARGE_FAMILY_CERTIFICATE,
            self::ASP_CERTIFICATE,
            self::TECH_PASSPORT,
            self::PENSION,
            self::RK_PASSPORT,
            self::STUDENT_CARD,
            self::VNZH,
            self::PCR_CERTIFICATE,
            self::LBG_CARD,
            self::INSURANCE_POLICY,
            self::HUNTER,
            self::ORALMAN,
            self::ATTORNEY,
        ];
    }
}
