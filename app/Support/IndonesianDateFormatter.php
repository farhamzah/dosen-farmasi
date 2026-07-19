<?php

namespace App\Support;

use Carbon\CarbonInterface;

class IndonesianDateFormatter
{
    private const MONTHS = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    public static function date(?CarbonInterface $date): string
    {
        if (! $date) {
            return 'Tanggal belum diisi';
        }

        return $date->format('j').' '.self::MONTHS[(int) $date->format('n')].' '.$date->format('Y');
    }

    public static function time(?CarbonInterface $date): string
    {
        if (! $date) {
            return '';
        }

        return $date->format('H.i');
    }

    public static function range(?CarbonInterface $start, ?CarbonInterface $end): string
    {
        if (! $start && ! $end) {
            return 'Waktu belum diisi';
        }

        if ($start && $end && $start->isSameDay($end)) {
            return self::date($start).' · '.self::time($start).'–'.self::time($end).' WIB';
        }

        if ($start && $end) {
            return self::date($start).', '.self::time($start).' – '.self::date($end).', '.self::time($end).' WIB';
        }

        return self::date($start ?: $end).' · '.self::time($start ?: $end).' WIB';
    }
}
