<?php

namespace App\Helpers;

class CurrencyFormatter
{
    public static function format(float $amount, bool $includeSymbol = true): string
    {
        $symbol = $includeSymbol ? '$' : '';

        if ($amount >= 1000000000) {
            return $symbol.number_format($amount / 1000000000, 1).'B';
        } elseif ($amount >= 1000000) {
            return $symbol.number_format($amount / 1000000, 1).'M';
        } elseif ($amount >= 1000) {
            return $symbol.number_format($amount / 1000, 1).'K';
        } else {
            return $symbol.number_format($amount, 0);
        }
    }

    public static function formatAverage(float $amount, bool $includeSymbol = true): string
    {
        $symbol = $includeSymbol ? '$' : '';

        if ($amount >= 1000000) {
            return $symbol.number_format($amount / 1000000, 1).'M';
        } elseif ($amount >= 1000) {
            return $symbol.number_format($amount / 1000, 0).'K';
        } else {
            return $symbol.number_format($amount, 0);
        }
    }
}
