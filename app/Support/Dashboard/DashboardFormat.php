<?php

namespace App\Support\Dashboard;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class DashboardFormat
{
    public static function currency(float | int | string | null $amount, string $currency = 'IDR'): string
    {
        $numericAmount = (float) ($amount ?? 0);

        if (strtoupper($currency) === 'IDR') {
            return 'Rp ' . number_format($numericAmount, 0, ',', '.');
        }

        return strtoupper($currency) . ' ' . number_format($numericAmount, 0, ',', '.');
    }

    public static function percent(float | int $value): string
    {
        return number_format((float) $value, 2, '.', '') . '%';
    }

    public static function shortDate(CarbonInterface | string | null $date, string $timezone = 'Asia/Jakarta'): string
    {
        return static::normalizeDate($date, $timezone)->locale('id')->translatedFormat('d M');
    }

    public static function monthYear(CarbonInterface | string | null $date, string $timezone = 'Asia/Jakarta'): string
    {
        return static::normalizeDate($date, $timezone)->locale('id')->translatedFormat('F Y');
    }

    public static function dayMonthTime(CarbonInterface | string | null $date, string $timezone = 'Asia/Jakarta'): string
    {
        return static::normalizeDate($date, $timezone)->locale('id')->translatedFormat('d M, H:i');
    }

    public static function fullDateTime(CarbonInterface | string | null $date, string $timezone = 'Asia/Jakarta'): string
    {
        $normalizedDate = static::normalizeDate($date, $timezone);

        return $normalizedDate->locale('id')->translatedFormat('d F Y, H:i') . ' ' . $normalizedDate->format('T');
    }

    public static function humanDiff(CarbonInterface | string | null $date, string $timezone = 'Asia/Jakarta'): string
    {
        return static::normalizeDate($date, $timezone)->locale('id')->diffForHumans();
    }

    public static function fileSize(int | null $bytes): string
    {
        $size = max((int) ($bytes ?? 0), 0);

        if ($size < 1024) {
            return $size . ' B';
        }

        if ($size < 1024 * 1024) {
            return number_format($size / 1024, 2, '.', '') . ' KB';
        }

        if ($size < 1024 * 1024 * 1024) {
            return number_format($size / 1024 / 1024, 2, '.', '') . ' MB';
        }

        return number_format($size / 1024 / 1024 / 1024, 2, '.', '') . ' GB';
    }

    public static function timezone(?string $timezone): string
    {
        return filled($timezone) ? $timezone : config('app.timezone', 'Asia/Jakarta');
    }

    protected static function normalizeDate(CarbonInterface | string | null $date, string $timezone): Carbon
    {
        return Carbon::parse($date ?? now())->timezone($timezone);
    }
}
