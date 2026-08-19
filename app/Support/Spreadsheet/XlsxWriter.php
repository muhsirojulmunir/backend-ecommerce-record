<?php

namespace App\Support\Spreadsheet;

use RuntimeException;

/**
 * Penulis berkas .xlsx sederhana tanpa library pihak ketiga.
 */
class XlsxWriter
{
    /**
 * @var array<int, array<int, string>>
 */
    private array $rows = [];

    /**
 * @var array<int, float> lebar kolom, indeks berbasis nol
 */
    private array $widths = [];

    /**
 * @var array<int, array{formula: string, lastRow: int}> dropdown per kolom
 */
    private array $dropdowns = [];

    private string $sheetName = 'Sheet1';

    private int $headerRows = 1;

    /**
 * @var array<int, true> indeks baris yang diberi gaya "petunjuk"
 */
    private array $hintRows = [];

    /**
 * @var array<int, true> indeks baris yang diberi gaya "contoh"
 */
    private array $exampleRows = [];

    public function setSheetName(string $name): static
    {
        // Excel melarang beberapa karakter pada nama sheet
        $this->sheetName = mb_substr(str_replace(['\\', '/', '*', '?', ':', '[', ']'], '', $name), 0, 31);

        return $this;
    }

    /**
 * @param array<int, string> $row
 */
    public function addRow(array $row): static
    {
        $this->rows[] = array_values($row);

        return $this;
    }

    /**
 * @param array<int, array<int, string>> $rows
 */
    public function addRows(array $rows): static
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }

        return $this;
    }

    /**
 * @param array<int, float> $widths
 */
    public function setWidths(array $widths): static
    {
        $this->widths = array_values($widths);

        return $this;
    }

    /** Jumlah baris teratas yang diberi gaya judul. */
    public function setHeaderRows(int $count): static
    {
        $this->headerRows = max(0, $count);

        return $this;
    }

    /** Tandai baris (indeks berbasis nol) sebagai baris petunjuk. */
    public function markHintRow(int $rowIndex): static
    {
        $this->hintRows[$rowIndex] = true;

        return $this;
    }

    /** Tandai baris (indeks berbasis nol) sebagai baris contoh. */
    public function markExampleRow(int $rowIndex): static
    {
        $this->exampleRows[$rowIndex] = true;

        return $this;
    }

    /**
 * Pasang dropdown pilihan pada satu kolom.
 *
 * @param int $column indeks kolom berbasis nol
 * @param array<int, string> $options
 */
    public function addDropdown(int $column, array $options, int $lastRow = 500): static
    {
        $clean = array_map(fn ($o) => str_replace([',', '"'], ' ', (string) $o), $options);
        $formula = '"' . implode(',', $clean) . '"';

        // Excel membatasi panjang rumus daftar inline
        if (mb_strlen($formula) > 255) {
            return $this;
        }

        $this->dropdowns[$column] = ['formula' => $formula, 'lastRow' => $lastRow];

        return $this;
    }

    /**
 * Dropdown yang isinya mengacu ke rentang sel lain, misalnya "Produk!$A$5:$A$504" — daftarnya ikut ...
 */
    public function addDropdownFromRange(int $column, string $range, int $lastRow = 500): static
    {
        $this->dropdowns[$column] = ['formula' => $range, 'lastRow' => $lastRow];

        return $this;
    }

    /**
     * Tulis berkas .xlsx ke path tujuan.
     */
    public function save(string $path): void
    {
        if (! class_exists(\ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP "zip" belum aktif, jadi berkas .xlsx tidak bisa dibuat.');
        }

        if (file_exists($path)) {
            @unlink($path);
        }

        $zip = new \ZipArchive();

        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Tidak bisa membuat berkas .xlsx di ' . $path);
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels());
        $zip->addFromString('xl/styles.xml', $this->styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheet());

        $zip->close();
    }

    // ─── Bagian-bagian berkas OOXML ───────────────────────────────────────────

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . $this->escape($this->sheetName) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function styles(): string
    {
        return self::styleSheetXml();
    }

    /**
     * Dua gaya: 0 = biasa, 1 = judul (tebal, putih, latar oranye, rata tengah).
     * Publik supaya workbook multi-sheet bisa memakai definisi gaya yang sama.
     */
    public static function styleSheetXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="0"/>'
            . '<fonts count="4">'
            . '<font><sz val="11"/><color rgb="FF0F172A"/><name val="Calibri"/><family val="2"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>'
            . '<font><i/><sz val="9"/><color rgb="FF1D4ED8"/><name val="Calibri"/><family val="2"/></font>'
            . '<font><i/><sz val="10"/><color rgb="FF64748B"/><name val="Calibri"/><family val="2"/></font>'
            . '</fonts>'
            . '<fills count="5">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFEA580C"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFEFF6FF"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border><left style="thin"><color rgb="FFCBD5E1"/></left><right style="thin"><color rgb="FFCBD5E1"/></right>'
            . '<top style="thin"><color rgb="FFCBD5E1"/></top><bottom style="thin"><color rgb="FFCBD5E1"/></bottom><diagonal/></border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="4">'
            // 0 — sel biasa
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1">'
            . '<alignment vertical="top" wrapText="1"/></xf>'
            // 1 — judul kolom
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">'
            . '<alignment horizontal="left" vertical="center" wrapText="1"/></xf>'
            // 2 — baris petunjuk
            . '<xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">'
            . '<alignment horizontal="left" vertical="top" wrapText="1"/></xf>'
            // 3 — baris contoh
            . '<xf numFmtId="0" fontId="3" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">'
            . '<alignment horizontal="left" vertical="top" wrapText="1"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function sheet(): string
    {
        return $this->sheetXml();
    }

    /**
 * XML satu worksheet.
 */
    public function sheetXml(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

        // 1. sheetViews — baris judul dibekukan agar tetap terlihat saat digulir
        if ($this->headerRows > 0) {
            $freezeAt = $this->headerRows + 1;
            // tabSelected sengaja tidak dipakai: kalau lebih dari satu sheet
            // menandainya, Excel bisa menganggap berkas bermasalah.
            $xml .= '<sheetViews><sheetView workbookViewId="0">'
                . '<pane ySplit="' . $this->headerRows . '" topLeftCell="A' . $freezeAt . '"'
                . ' activePane="bottomLeft" state="frozen"/>'
                . '<selection pane="bottomLeft" activeCell="A' . $freezeAt . '" sqref="A' . $freezeAt . '"/>'
                . '</sheetView></sheetViews>';
        }

        // 2. cols — lebar kolom
        if (! empty($this->widths)) {
            $xml .= '<cols>';
            foreach ($this->widths as $i => $width) {
                $xml .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $width . '" customWidth="1"/>';
            }
            $xml .= '</cols>';
        }

        // 3. sheetData — isi sel
        $xml .= '<sheetData>';

        foreach ($this->rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 1;
            $style     = $this->styleForRow($rowIndex);
            $height    = $this->heightForRow($rowIndex);

            $xml .= '<row r="' . $rowNumber . '"' . $height . '>';

            foreach ($row as $colIndex => $value) {
                $value = (string) $value;

                if ($value === '') {
                    continue;
                }

                $reference = $this->columnLetter($colIndex) . $rowNumber;

                // Semua nilai ditulis sebagai teks inline supaya Excel tidak
                // mengubah "38" atau "0812..." jadi angka/tanggal
                $xml .= '<c r="' . $reference . '" s="' . $style . '" t="inlineStr">'
                    . '<is><t xml:space="preserve">' . $this->escape($value) . '</t></is></c>';
            }

            $xml .= '</row>';
        }

        $xml .= '</sheetData>';

        // 4. dataValidations — dropdown
        $xml .= $this->dataValidations();
        $xml .= '</worksheet>';

        return $xml;
    }

    private function styleForRow(int $rowIndex): int
    {
        if ($rowIndex < $this->headerRows) {
            return 1; // judul
        }

        if (isset($this->hintRows[$rowIndex])) {
            return 2; // baris petunjuk
        }

        if (isset($this->exampleRows[$rowIndex])) {
            return 3; // baris contoh
        }

        return 0;
    }

    private function heightForRow(int $rowIndex): string
    {
        if ($rowIndex < $this->headerRows) {
            return ' ht="30" customHeight="1"';
        }

        if (isset($this->hintRows[$rowIndex])) {
            return ' ht="46" customHeight="1"';
        }

        return '';
    }

    private function dataValidations(): string
    {
        if (empty($this->dropdowns)) {
            return '';
        }

        $xml = '<dataValidations count="' . count($this->dropdowns) . '">';

        // Dropdown dipasang mulai baris isian asli — di bawah judul,
        // baris petunjuk, dan baris contoh.
        $firstDataRow = max(
            $this->headerRows,
            empty($this->hintRows) ? 0 : max(array_keys($this->hintRows)) + 1,
            empty($this->exampleRows) ? 0 : max(array_keys($this->exampleRows)) + 1,
        ) + 1;

        foreach ($this->dropdowns as $column => $config) {
            $letter = $this->columnLetter($column);
            $lastRow = max($config['lastRow'], $firstDataRow);
            $range  = $letter . $firstDataRow . ':' . $letter . $lastRow;

            $xml .= '<dataValidation type="list" allowBlank="1" showInputMessage="1" showErrorMessage="0"'
                . ' sqref="' . $range . '">'
                . '<formula1>' . $this->escape($config['formula']) . '</formula1>'
                . '</dataValidation>';
        }

        return $xml . '</dataValidations>';
    }

    /** 0 → A, 25 → Z, 26 → AA */
    private function columnLetter(int $index): string
    {
        $letter = '';

        for ($i = $index; $i >= 0; $i = intdiv($i, 26) - 1) {
            $letter = chr(65 + ($i % 26)) . $letter;
        }

        return $letter;
    }

    private function escape(string $value): string
    {
        // Buang karakter kontrol yang membuat XML tidak valid
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value) ?? $value;

        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
