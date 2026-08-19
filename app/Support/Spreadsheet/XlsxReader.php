<?php

namespace App\Support\Spreadsheet;

use RuntimeException;

/**
 * Pembaca berkas .xlsx dan .csv tanpa library pihak ketiga.
 */
class XlsxReader
{
    /**
 * Baca sheet pertama menjadi array dua dimensi (baris => kolom => nilai teks).
 *
 * @throws RuntimeException kalau berkas tidak bisa dibaca
 */
    public static function read(string $path): array
    {
        $sheets = self::readAll($path);

        return $sheets === [] ? [] : reset($sheets);
    }

    /**
 * Baca semua sheet: [nama sheet => baris].
 *
 * @throws RuntimeException kalau berkas tidak bisa dibaca
 */
    public static function readAll(string $path): array
    {
        if (! is_readable($path)) {
            throw new RuntimeException('Berkas tidak ditemukan atau tidak bisa dibaca.');
        }

        return self::looksLikeZip($path)
            ? self::readXlsxSheets($path)
            : ['Sheet1' => self::readCsv($path)];
    }

    /**
     * Ambil satu sheet berdasarkan nama (tidak peka huruf besar/kecil).
     * Kalau tidak ketemu, kembalikan array kosong.
     */
    public static function sheet(array $sheets, string $name): array
    {
        foreach ($sheets as $sheetName => $rows) {
            if (strcasecmp(trim((string) $sheetName), $name) === 0) {
                return $rows;
            }
        }

        return [];
    }

    /** Berkas .xlsx selalu diawali tanda tangan ZIP "PK\x03\x04". */
    private static function looksLikeZip(string $path): bool
    {
        $handle = fopen($path, 'rb');

        if (! $handle) {
            return false;
        }

        $magic = fread($handle, 4);
        fclose($handle);

        return $magic === "PK\x03\x04";
    }

    // ─── XLSX ─────────────────────────────────────────────────────────────────

    private static function readXlsxSheets(string $path): array
    {
        if (! class_exists(\ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP "zip" belum aktif, jadi berkas .xlsx tidak bisa dibaca. Silakan unggah berkas .csv sebagai gantinya.');
        }

        $zip = new \ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('Berkas .xlsx rusak atau tidak bisa dibuka.');
        }

        try {
            $shared = self::readSharedStrings($zip);
            $sheets = [];

            foreach (self::sheetPaths($zip) as $name => $sheetPath) {
                $xml = self::loadXml($zip->getFromName($sheetPath));
                $sheets[$name] = $xml === null ? [] : self::extractRows($xml, $shared);
            }

            if (empty($sheets)) {
                throw new RuntimeException('Berkas tidak berisi sheet yang bisa dibaca.');
            }

            return $sheets;
        } finally {
            $zip->close();
        }
    }

    /**
     * Tabel string bersama — sel bertipe "s" merujuk ke indeks di tabel ini.
     */
    private static function readSharedStrings(\ZipArchive $zip): array
    {
        $raw = $zip->getFromName('xl/sharedStrings.xml');

        if ($raw === false) {
            return [];
        }

        $xml = self::loadXml($raw);

        if ($xml === null) {
            return [];
        }

        $strings = [];

        foreach ($xml->si as $item) {
            $strings[] = self::flattenText($item);
        }

        return $strings;
    }

    /**
 * Petakan nama sheet ke path XML-nya lewat workbook.xml + rels.
 *
 * @return array<string, string>
 */
    private static function sheetPaths(\ZipArchive $zip): array
    {
        $workbook = self::loadXml($zip->getFromName('xl/workbook.xml'));
        $rels     = self::loadXml($zip->getFromName('xl/_rels/workbook.xml.rels'));

        if (! $workbook || ! $rels || ! isset($workbook->sheets->sheet)) {
            return ['Sheet1' => 'xl/worksheets/sheet1.xml'];
        }

        // Id relasi => target berkas
        $targets = [];
        foreach ($rels->Relationship as $relationship) {
            $target = ltrim((string) $relationship['Target'], '/');
            $targets[(string) $relationship['Id']] = str_starts_with($target, 'xl/') ? $target : 'xl/' . $target;
        }

        $paths = [];
        $fallback = 1;

        foreach ($workbook->sheets->sheet as $sheet) {
            $name = (string) $sheet['name'];
            $id   = (string) $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')->id;

            $paths[$name !== '' ? $name : 'Sheet' . $fallback] =
                $targets[$id] ?? 'xl/worksheets/sheet' . $fallback . '.xml';

            $fallback++;
        }

        return $paths ?: ['Sheet1' => 'xl/worksheets/sheet1.xml'];
    }

    /**
 * Ubah elemen <sheetData> menjadi array baris.
 */
    private static function extractRows(\SimpleXMLElement $xml, array $shared): array
    {
        $rows = [];

        foreach ($xml->sheetData->row as $row) {
            $rowIndex = (int) ($row['r'] ?? (count($rows) + 1));
            $cells    = [];

            foreach ($row->c as $cell) {
                $reference = (string) ($cell['r'] ?? '');
                $column    = $reference !== ''
                    ? self::columnIndex($reference)
                    : count($cells);

                $cells[$column] = self::cellValue($cell, $shared);
            }

            if (empty($cells)) {
                $rows[$rowIndex - 1] = [];
                continue;
            }

            // Isi celah kolom yang kosong
            $width  = max(array_keys($cells)) + 1;
            $padded = [];

            for ($i = 0; $i < $width; $i++) {
                $padded[$i] = $cells[$i] ?? '';
            }

            $rows[$rowIndex - 1] = $padded;
        }

        if (empty($rows)) {
            return [];
        }

        // Samakan panjang tiap baris & rapikan indeks
        $width = max(array_map('count', $rows));
        $result = [];

        for ($i = 0; $i <= max(array_keys($rows)); $i++) {
            $row = $rows[$i] ?? [];
            $result[] = array_pad($row, $width, '');
        }

        return $result;
    }

    private static function cellValue(\SimpleXMLElement $cell, array $shared): string
    {
        $type = (string) ($cell['t'] ?? '');

        return match ($type) {
            's'         => $shared[(int) $cell->v] ?? '',
            'inlineStr' => self::flattenText($cell->is),
            'str'       => (string) $cell->v,
            'b'         => ((string) $cell->v) === '1' ? 'TRUE' : 'FALSE',
            default     => isset($cell->v) ? (string) $cell->v : '',
        };
    }

    /**
     * Gabungkan seluruh <t>, termasuk yang terpecah dalam <r> (rich text).
     */
    private static function flattenText(?\SimpleXMLElement $node): string
    {
        if ($node === null) {
            return '';
        }

        $text = '';

        if (isset($node->t)) {
            foreach ($node->t as $t) {
                $text .= (string) $t;
            }
        }

        if (isset($node->r)) {
            foreach ($node->r as $run) {
                foreach ($run->t as $t) {
                    $text .= (string) $t;
                }
            }
        }

        return $text;
    }

    /** "C7" → 2 (indeks kolom berbasis nol). */
    private static function columnIndex(string $reference): int
    {
        preg_match('/^([A-Z]+)/i', $reference, $match);

        $letters = strtoupper($match[1] ?? 'A');
        $index   = 0;

        foreach (str_split($letters) as $letter) {
            $index = $index * 26 + (ord($letter) - 64);
        }

        return $index - 1;
    }

    private static function loadXml(string|false $raw): ?\SimpleXMLElement
    {
        if ($raw === false || trim($raw) === '') {
            return null;
        }

        $previous = libxml_use_internal_errors(true);

        // LIBXML_NONET mencegah pengambilan entitas eksternal dari berkas yang diunggah
        $xml = simplexml_load_string($raw, \SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOENT * 0);

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $xml === false ? null : $xml;
    }

    // ─── CSV ──────────────────────────────────────────────────────────────────

    private static function readCsv(string $path): array
    {
        $raw = file_get_contents($path);

        if ($raw === false) {
            throw new RuntimeException('Berkas CSV tidak bisa dibaca.');
        }

        // Buang BOM UTF-8 kalau ada
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);

        // Excel Indonesia sering menyimpan CSV dengan titik koma
        $delimiter = self::guessDelimiter($raw);

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $raw);
        rewind($handle);

        $rows = [];

        while (($row = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
            $rows[] = array_map(fn ($cell) => (string) $cell, $row);
        }

        fclose($handle);

        if (empty($rows)) {
            return [];
        }

        $width = max(array_map('count', $rows));

        return array_map(fn ($row) => array_pad($row, $width, ''), $rows);
    }

    private static function guessDelimiter(string $raw): string
    {
        $firstLine = strtok($raw, "\n") ?: '';

        return substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
    }
}
