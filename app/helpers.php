<?php

if (! function_exists('format_rupiah')) {
    /**
     * Format angka menjadi format mata uang Rupiah (contoh: Rp 45.000).
     */
    function format_rupiah(float|int|string|null $angka): string
    {
        return 'Rp '.number_format((float) ($angka ?? 0), 0, ',', '.');
    }
}

if (! function_exists('format_angka')) {
    /**
     * Format angka dengan pemisah ribuan Indonesia (contoh: 1.250).
     */
    function format_angka(float|int|string|null $angka): string
    {
        return number_format((float) ($angka ?? 0), 0, ',', '.');
    }
}
