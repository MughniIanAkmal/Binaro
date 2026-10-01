<?php

namespace App\Services;

use ZipArchive;

class ExcelService
{
    /**
     * Membaca baris data dari file Excel (.xlsx) atau CSV (.csv).
     * Mengembalikan array 2D berisi nilai string per sel.
     */
    public static function parseFile(string $filePath, ?string $extension = null): array
    {
        $ext = strtolower($extension ?: pathinfo($filePath, PATHINFO_EXTENSION));
        if ($ext === 'csv') {
            return self::parseCsv($filePath);
        }
        return self::parseRows($filePath);
    }

    /**
     * Membaca file format CSV (.csv) dengan deteksi delimiter koma atau titik-koma.
     */
    public static function parseCsv(string $filePath): array
    {
        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            // Cek dan lewati UTF-8 BOM jika ada
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                // Jika terparse 1 kolom tapi mengandung titik koma, parse dengan titik koma
                if (count($data) === 1 && strpos($data[0], ';') !== false) {
                    $data = str_getcsv($data[0], ';');
                }
                $rows[] = array_map('trim', $data);
            }
            fclose($handle);
        }
        return $rows;
    }

    /**
     * Membaca baris data dari file Excel (.xlsx) tanpa package eksternal.
     * Mengembalikan array 2D berisi nilai string per sel.
     */
    public static function parseRows(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        // 1. Baca shared strings
        $strings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml) {
            $xml = simplexml_load_string($sharedStringsXml);
            if ($xml) {
                foreach ($xml->si as $val) {
                    if (isset($val->t)) {
                        $strings[] = (string) $val->t;
                    } elseif (isset($val->r)) {
                        $t = '';
                        foreach ($val->r as $r) {
                            $t .= (string) $r->t;
                        }
                        $strings[] = $t;
                    } else {
                        $strings[] = '';
                    }
                }
            }
        }

        // 2. Baca sheet data
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $rows = [];
        if ($sheetXml) {
            $xml = simplexml_load_string($sheetXml);
            if ($xml && isset($xml->sheetData->row)) {
                foreach ($xml->sheetData->row as $row) {
                    $rowData = [];
                    foreach ($row->c as $cell) {
                        $cellRef = (string) $cell['r'];
                        $colLetter = preg_replace('/[0-9]/', '', $cellRef);
                        $colIndex = 0;
                        for ($i = 0; $i < strlen($colLetter); $i++) {
                            $colIndex = $colIndex * 26 + (ord(strtoupper($colLetter[$i])) - ord('A') + 1);
                        }
                        $colIndex -= 1;

                        $val = (string) $cell->v;
                        if ((string) $cell['t'] === 's' && isset($strings[(int) $val])) {
                            $val = $strings[(int) $val];
                        }
                        $rowData[$colIndex] = trim($val);
                    }
                    ksort($rowData);
                    $rows[] = array_values($rowData);
                }
            }
        }

        $zip->close();
        return $rows;
    }

    /**
     * Membuat template file Excel (.xlsx) untuk bank soal kuis/ujian.
     */
    public static function generateQuizTemplate(): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'tpl_') . '.xlsx';
        $zip = new ZipArchive();

        if ($zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            // [Content_Types].xml
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
</Types>');

            // _rels/.rels
            $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

            // xl/_rels/workbook.xml.rels
            $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
</Relationships>');

            // xl/workbook.xml
            $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Sheet1" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>');

            // String data
            $sampleStrings = [
                'pertanyaan', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'kunci_jawaban',
                'Berapakah 15 + 25?', '30', '40', '45', '50', 'B',
                'Ibu kota negara Indonesia adalah...', 'Surabaya', 'Bandung', 'Nusantara', 'Semarang', 'C',
                'Hasil dari 8 x 7 adalah...', '54', '56', '58', '60', 'B',
            ];

            $sst = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
            $sst .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sampleStrings) . '" uniqueCount="' . count($sampleStrings) . '">';
            foreach ($sampleStrings as $s) {
                $sst .= '<si><t>' . htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8') . '</t></si>';
            }
            $sst .= '</sst>';
            $zip->addFromString('xl/sharedStrings.xml', $sst);

            // Sheet1
            $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
            $sheet .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';

            // Header row
            $sheet .= '<row r="1">';
            for ($i = 0; $i < 6; $i++) {
                $col = chr(65 + $i);
                $sheet .= '<c r="' . $col . '1" t="s"><v>' . $i . '</v></c>';
            }
            $sheet .= '</row>';

            // Sample rows
            $dataIdx = 6;
            for ($row = 2; $row <= 4; $row++) {
                $sheet .= '<row r="' . $row . '">';
                for ($col = 0; $col < 6; $col++) {
                    $colChar = chr(65 + $col);
                    $sheet .= '<c r="' . $colChar . $row . '" t="s"><v>' . $dataIdx . '</v></c>';
                    $dataIdx++;
                }
                $sheet .= '</row>';
            }

            $sheet .= '</sheetData></worksheet>';
            $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
            $zip->close();
        }

        $content = file_get_contents($tempPath);
        @unlink($tempPath);
        return $content;
    }
}
