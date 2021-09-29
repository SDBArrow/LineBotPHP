<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


$mpdf = new \Mpdf\Mpdf(['mode' => 'UTF-8']); 
$mpdf -> autoLangToFont =true;
$mpdf -> autoScriptToLang = true;

$new_excel_path = "sign_table.xlsx";
$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
$spreadsheet = $reader->load("$new_excel_path");

//Creazione del writer
$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Tcpdf');

//Salvataggio del pfd
$name = 'test';
$pdf_path = $name.'.pdf';
$writer->save($pdf_path);