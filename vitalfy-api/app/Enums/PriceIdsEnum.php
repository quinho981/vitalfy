<?php

namespace App\Enums;

enum PriceIdsEnum: string
{
    case PRO_MONTHLY = 'pro_monthly';
    case PRO_SEMESTER = 'pro_semester';
    case PRO_ANNUAL = 'pro_annual';

    public function priceId(): string
    {
        return match($this) {
            self::PRO_MONTHLY => 'price_1TvoziLBykDo8qwxwt4Sopd6',
            self::PRO_SEMESTER => 'price_1Tvp0vLBykDo8qwxNinL1xrh',
            self::PRO_ANNUAL => 'price_1Tvp1RLBykDo8qwxctOtFPSu',
        };
    }

    public function label(): string
    {
        return match($this) {
            self::PRO_MONTHLY => 'Vitalfy Profissional Mensal',
            self::PRO_SEMESTER => 'Vitalfy Profissional Semestral',
            self::PRO_ANNUAL => 'Vitalfy Profissional Anual',
        };
    }
}
