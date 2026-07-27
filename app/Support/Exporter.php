<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Turns a header row plus an iterable of rows into a download.
 *
 * Rows are taken as an iterable so callers can hand over a lazy collection or a
 * generator and never hold a whole report in memory — the audit trail is the
 * one table here that genuinely grows without bound.
 *
 * PDF is produced as a print-ready HTML document rather than by pulling in a
 * PDF engine: the browser's own "Save as PDF" gives a better result than a
 * bundled renderer for tabular reports, and it keeps the dependency list short.
 */
class Exporter
{
    /** Cell fill for the header row. */
    private const HEADER_BG = 'FF4F46E5';

    /**
     * @param  list<string>  $headers
     * @param  iterable<array<int, mixed>>  $rows
     */
    public static function xlsx(string $filename, array $headers, iterable $rows, ?string $title = null): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $sheet->setTitle(mb_substr($title ?: 'Export', 0, 31));

        $sheet->fromArray($headers, null, 'A1');

        $lastColumn = self::columnLetter(count($headers));

        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::HEADER_BG]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->freezePane('A2');

        $rowNumber = 2;

        foreach ($rows as $row) {
            // Long free text is written explicitly as a string so a value like
            // "0123" or "=SUM(..)" is never coerced or treated as a formula.
            foreach (array_values($row) as $i => $value) {
                $cell = self::columnLetter($i + 1) . $rowNumber;

                if (is_int($value) || is_float($value)) {
                    $sheet->setCellValue($cell, $value);
                } else {
                    $sheet->setCellValueExplicit(
                        $cell,
                        (string) ($value ?? ''),
                        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                    );
                }
            }

            $rowNumber++;
        }

        $lastRow = max(1, $rowNumber - 1);

        if ($lastRow > 1) {
            $sheet->getStyle("A2:{$lastColumn}{$lastRow}")->applyFromArray([
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => false],
            ]);
        }

        $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");

        foreach (range(1, count($headers)) as $i) {
            $sheet->getColumnDimension(self::columnLetter($i))->setAutoSize(true);
        }

        return new StreamedResponse(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . self::safeName($filename) . '.xlsx"',
            'Cache-Control'       => 'max-age=0, must-revalidate',
            'Pragma'              => 'public',
        ]);
    }

    /**
     * @param  list<string>  $headers
     * @param  iterable<array<int, mixed>>  $rows
     */
    public static function csv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return new StreamedResponse(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');

            // BOM so Excel opens UTF-8 correctly instead of mangling accents.
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, $headers);

            foreach ($rows as $row) {
                fputcsv($out, array_map(static fn ($v) => self::csvSafe($v), array_values($row)));
            }

            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . self::safeName($filename) . '.csv"',
            'Cache-Control'       => 'max-age=0, must-revalidate',
        ]);
    }

    /**
     * A print-ready page. The browser's print dialog turns it into a PDF.
     *
     * @param  list<string>  $headers
     * @param  iterable<array<int, mixed>>  $rows
     */
    public static function printable(string $filename, string $title, array $headers, iterable $rows, array $meta = [])
    {
        return response()->view('backend.exports.printable', [
            'title'    => $title,
            'filename' => self::safeName($filename),
            'headers'  => $headers,
            'rows'     => $rows,
            'meta'     => $meta,
        ]);
    }

    /**
     * Dispatch on the requested format. Anything unrecognised falls back to
     * xlsx, which is the mandated one.
     *
     * @param  list<string>  $headers
     * @param  iterable<array<int, mixed>>  $rows
     */
    public static function make(
        ?string $format,
        string $filename,
        string $title,
        array $headers,
        iterable $rows,
        array $meta = [],
    ) {
        return match (strtolower((string) $format)) {
            'csv'          => self::csv($filename, $headers, $rows),
            'pdf', 'print' => self::printable($filename, $title, $headers, $rows, $meta),
            default        => self::xlsx($filename, $headers, $rows, $title),
        };
    }

    /** A leading =, +, - or @ makes a spreadsheet treat text as a formula. */
    private static function csvSafe(mixed $value): string
    {
        $string = (string) ($value ?? '');

        return $string !== '' && in_array($string[0], ['=', '+', '-', '@'], true)
            ? "'" . $string
            : $string;
    }

    private static function columnLetter(int $index): string
    {
        $letter = '';

        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)) . $letter;
            $index  = intdiv($index, 26);
        }

        return $letter ?: 'A';
    }

    private static function safeName(string $name): string
    {
        $name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $name) ?: 'export';

        return $name . '_' . now()->format('Ymd_His');
    }
}
