<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

abstract class BaseReportExport extends DefaultValueBinder implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithStrictNullComparison
{
    public const NUMBER_FORMAT_THOUSANDS = '#,##0';

    /**
     * Mitigasi injeksi rumus Excel (PRD §15 & TC-38):
     * Sel teks yang diawali =, +, -, atau @ diberi awalan tanda kutip tunggal (').
     */
    public static function sanitizeText(?string $value): string
    {
        $text = (string) ($value ?? '');
        if ($text === '') {
            return '';
        }

        if (in_array($text[0], ['=', '+', '-', '@'], true)) {
            return "'".$text;
        }

        return $text;
    }

    /**
     * Pastikan angka disimpan sebagai tipe numerik asli (bukan teks) dan teks berbahaya tidak dieksekusi sebagai rumus.
     */
    public function bindValue(Cell $cell, $value): bool
    {
        if (is_int($value) || is_float($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_NUMERIC);

            return true;
        }

        if (is_string($value) && $value !== '') {
            if (in_array($value[0], ['=', '+', '-', '@'], true)) {
                $cell->setValueExplicit("'".$value, DataType::TYPE_STRING);

                return true;
            }

            if ($value[0] === "'" && isset($value[1]) && in_array($value[1], ['=', '+', '-', '@'], true)) {
                $cell->setValueExplicit($value, DataType::TYPE_STRING);

                return true;
            }
        }

        return parent::bindValue($cell, $value);
    }
}
