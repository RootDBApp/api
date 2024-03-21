<?php

namespace App\Enums;

enum EnumFrequency: string
{
    case EVERY_FIFTEEN_MINUTES = 'everyFifteenMinutes';
    case EVERY_THIRTY_MINUTES = 'everyThirtyMinutes';
    case HOURLY_AT = 'hourlyAt';
    case DAILY_AT = 'dailyAt';
    case WEEKLY_ON = 'weeklyOn';
    case MONTHLY_ON = 'monthlyOn';
}
