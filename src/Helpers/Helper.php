<?php

namespace MenqzAdmin\Admin\Helpers;

class Helper
{
    static function addVersionFile($fileName)
    {
        $jsPath = public_path($fileName);
        $version = file_exists($jsPath) ? filemtime($jsPath) : time();
        return asset($fileName) . '?v=' . $version;
    }

    static function formatCurrency($value, $precision = 2)
    {
        return number_format($value, $precision, ',', '.');
    }

    static function currencyToFloat($value)
    {
        if (!empty($value) && !is_null($value)) {
            $valueNormalized = str_replace([',', ' ', ' '], ['.', '', ''], $value);
            $valueNormalized = str_replace(' ', '', $valueNormalized);
            return (float) $valueNormalized;
        }

        return 0.0;
    }
}
