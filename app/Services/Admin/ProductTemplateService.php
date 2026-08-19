<?php

namespace App\Services\Admin;

use App\Models\Category;
use App\Support\Spreadsheet\XlsxWriter;

/**
 * Membuat berkas template .xlsx untuk impor produk massal.
 */
class ProductTemplateService
{
    private const PRODUCT_WIDTHS = [38, 22, 52, 16, 16, 12, 14, 18, 16, 15, 14, 14, 18];
    private const VARIANT_WIDTHS = [38, 20, 14, 12, 18, 24];

    /** Penanda baris bantuan; baris berawalan ini dilewati saat impor. */
    public const HINT_MARK    = 'PETUNJUK:';
    public const EXAMPLE_MARK = 'CONTOH:';

    /**
     * Tulis template ke path tujuan.
     */
    public function writeTo(string $path): void
    {
        // Sheet "Varian" ditulis lebih dulu ke berkas terpisah, lalu digabung —
        // XlsxWriter menulis satu sheet per berkas, jadi dua sheet dibuat
        // dengan menuliskan keduanya lewat helper di bawah.
        $this->writeWorkbook($path);
    }

    // ─── Isi tiap sheet ───────────────────────────────────────────────────────

    /**
 * Baris sheet "Produk": judul kolom, baris petunjuk, lalu dua contoh.
 */
    private function productRows(): array
    {
        $header = [];
        $hints  = [];

        foreach (ProductImportService::COLUMNS as [$label, $required, $hint]) {
            $header[] = $required ? $label . ' *' : $label;
            $hints[]  = ($required ? '[WAJIB] ' : '[opsional] ') . $hint;
        }

        $hints[0] = self::HINT_MARK . ' ' . $hints[0];

        return [
            $header,
            $hints,
            [
                self::EXAMPLE_MARK . ' Sepatu Sekolah Hitam Anak', 'Sepatu Sekolah',
                'Sepatu sekolah bahan kanvas, jahitan kuat, nyaman dipakai seharian.',
                '150000', '200000', '18', 'aktif', 'Kanvas', '600', '30', '20', '12', 'tidak',
            ],
            [
                self::EXAMPLE_MARK . ' Sandal Jepit Dewasa', 'Sandal',
                'Sandal jepit karet, ringan dan tidak licin.',
                '45000', '', '120', 'aktif', 'Karet', '250', '28', '10', '4', 'ya',
            ],
        ];
    }

    /**
     * Baris sheet "Varian": judul, petunjuk, lalu contoh beberapa ukuran
     * dengan SKU berbeda — sesuai cara toko memberi kode per ukuran.
     */
    private function variantRows(): array
    {
        $header = [];
        $hints  = [];

        foreach (ProductImportService::VARIANT_COLUMNS as [$label, $required, $hint]) {
            $header[] = $required ? $label . ' *' : $label;
            $hints[]  = ($required ? '[WAJIB] ' : '[opsional] ') . $hint;
        }

        $hints[0] = self::HINT_MARK . ' ' . $hints[0];

        return [
            $header,
            $hints,
            [self::EXAMPLE_MARK . ' Sepatu Sekolah Hitam Anak', 'Hitam', '37', '6',  '0',    'REC-SSH-HTM-37'],
            [self::EXAMPLE_MARK . ' Sepatu Sekolah Hitam Anak', 'Hitam', '38', '6',  '0',    'REC-SSH-HTM-38'],
            [self::EXAMPLE_MARK . ' Sepatu Sekolah Hitam Anak', 'Hitam', '39', '6',  '5000', 'REC-SSH-HTM-39'],
            [self::EXAMPLE_MARK . ' Sandal Jepit Dewasa',       'Biru',  '40', '60', '0',    'REC-SJD-BIR-40'],
            [self::EXAMPLE_MARK . ' Sandal Jepit Dewasa',       'Biru',  '41', '60', '0',    'REC-SJD-BIR-41'],
        ];
    }

    // ─── Penulisan workbook dua sheet ─────────────────────────────────────────

    /**
     * XlsxWriter menulis satu sheet; untuk dua sheet, workbook dirakit
     * langsung di sini memakai dua instance writer sebagai sumber XML.
     */
    private function writeWorkbook(string $path): void
    {
        if (! class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('Ekstensi PHP "zip" belum aktif, jadi template .xlsx tidak bisa dibuat.');
        }

        $productSheet = (new XlsxWriter())
            ->setSheetName('Produk')
            ->addRows($this->productRows())
            ->setWidths(self::PRODUCT_WIDTHS)
            ->setHeaderRows(1)
            ->markHintRow(1)        // baris 2 = petunjuk
            ->markExampleRow(2)     // baris 3 & 4 = contoh
            ->markExampleRow(3);

        $statusIndex   = array_search('status', array_keys(ProductImportService::COLUMNS), true);
        $featuredIndex = array_search('unggulan', array_keys(ProductImportService::COLUMNS), true);
        $categoryIndex = array_search('kategori', array_keys(ProductImportService::COLUMNS), true);

        $productSheet->addDropdown($statusIndex, ['aktif', 'nonaktif', 'habis']);
        $productSheet->addDropdown($featuredIndex, ['ya', 'tidak']);

        // Kategori yang sudah ada ditawarkan sebagai dropdown, tapi admin
        // tetap bebas mengetik kategori baru — dropdown tidak memaksa.
        $categories = Category::orderBy('name')->pluck('name')->take(40)->all();
        if (! empty($categories)) {
            $productSheet->addDropdown($categoryIndex, $categories);
        }

        $variantSheet = (new XlsxWriter())
            ->setSheetName('Varian')
            ->addRows($this->variantRows())
            ->setWidths(self::VARIANT_WIDTHS)
            ->setHeaderRows(1)
            ->markHintRow(1)
            ->markExampleRow(2)->markExampleRow(3)
            ->markExampleRow(4)->markExampleRow(5)->markExampleRow(6);

        // Kolom "Nama Produk" di sheet Varian mengambil pilihan langsung dari
        // sheet Produk, jadi admin tinggal memilih dan tidak mungkin salah ketik.
        // Baris 5 ke bawah = area isian asli (baris 1-4 judul, petunjuk, contoh).
        $variantSheet->addDropdownFromRange(0, 'Produk!$A$5:$A$504', 504);

        $this->zipWorkbook($path, [
            ['name' => 'Produk', 'writer' => $productSheet],
            ['name' => 'Varian', 'writer' => $variantSheet],
        ]);
    }

    /**
 * Rakit arsip .xlsx berisi beberapa sheet.
 *
 * @param array<int, array{name: string, writer: XlsxWriter}> $sheets
 */
    private function zipWorkbook(string $path, array $sheets): void
    {
        if (file_exists($path)) {
            @unlink($path);
        }

        $zip = new \ZipArchive();

        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Tidak bisa membuat berkas template di ' . $path);
        }

        $count = count($sheets);

        // [Content_Types].xml
        $overrides = '';
        for ($i = 1; $i <= $count; $i++) {
            $overrides .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml"'
                . ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        $zip->addFromString('[Content_Types].xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . $overrides
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>'
        );

        $zip->addFromString('_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>'
        );

        // workbook.xml + rels
        $sheetTags = '';
        $relTags   = '';
        foreach ($sheets as $i => $sheet) {
            $n = $i + 1;
            $name = htmlspecialchars($sheet['name'], ENT_QUOTES | ENT_XML1, 'UTF-8');
            $sheetTags .= '<sheet name="' . $name . '" sheetId="' . $n . '" r:id="rId' . $n . '"/>';
            $relTags .= '<Relationship Id="rId' . $n . '"'
                . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"'
                . ' Target="worksheets/sheet' . $n . '.xml"/>';
        }

        $relTags .= '<Relationship Id="rIdStyles"'
            . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"'
            . ' Target="styles.xml"/>';

        $zip->addFromString('xl/workbook.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . $sheetTags . '</sheets></workbook>'
        );

        $zip->addFromString('xl/_rels/workbook.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $relTags . '</Relationships>'
        );

        $zip->addFromString('xl/styles.xml', XlsxWriter::styleSheetXml());

        foreach ($sheets as $i => $sheet) {
            $zip->addFromString('xl/worksheets/sheet' . ($i + 1) . '.xml', $sheet['writer']->sheetXml());
        }

        $zip->close();
    }
}
