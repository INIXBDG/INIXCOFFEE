<?php

if (! function_exists('formatRupiah')) {
    /**
     * Format number to Rupiah currency format
     *
     * @param float|int $value
     * @param bool $withPrefix
     * @return string
     */
    function formatRupiah($value, $withPrefix = true) {
        $formatted = number_format($value, 0, ',', '.');
        return $withPrefix ? 'Rp. ' . $formatted : $formatted;
    }
}
