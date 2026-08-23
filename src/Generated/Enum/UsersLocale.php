<?php

declare(strict_types=1);

namespace Clockster\Generated\Enum;

/**
 * What `locale` is allowed to be.
 *
 * Sent in a $clockster->users->upsert() body.
 *
 * Constants rather than the cases of an enum, so one goes wherever the string goes:
 * `UsersLocale::EN` is `'en'`, and a static analyser reads the two as one value. Closed on the way
 * in and only there — an answer naming something this class does not is still a string, and still
 * reaches you.
 */
final class UsersLocale
{
    public const EN = 'en';
    public const RU = 'ru';
    public const KK = 'kk';
    public const UK = 'uk';
    public const ID = 'id';
    public const UZ = 'uz';
    public const AZ = 'az';
    public const FR = 'fr';
    public const VI = 'vi';
    public const ZH = 'zh';

    /** @return list<'en'|'ru'|'kk'|'uk'|'id'|'uz'|'az'|'fr'|'vi'|'zh'> */
    public static function values(): array
    {
        return [
            self::EN,
            self::RU,
            self::KK,
            self::UK,
            self::ID,
            self::UZ,
            self::AZ,
            self::FR,
            self::VI,
            self::ZH,
        ];
    }
}
