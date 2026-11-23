<?php

if (!function_exists('transform_numbers')) {
    function transform_numbers($str)
    {
        $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.'];
        $eastern = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', ','];
        return str_replace($western, $eastern, $str);
    }
}

if (!function_exists('transform_numeric_value')) {
    function transform_numeric_value($number)
    {
        $number = (float) $number;

        $isNegative = $number < 0;

        $number = (int) round(abs($number));

        $formatted = number_format($number);

        $formatted = transform_numbers($formatted);

        return $isNegative ? '-' . $formatted : $formatted;
    }
}

if (! function_exists('round_to_nearest_tenth')) {
    function round_to_nearest_tenth($number): int
    {
        return (int) (round($number / 10) * 10);
    }
}
