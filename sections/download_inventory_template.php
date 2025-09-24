<?php
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="plantilla_inventario.xlsx"');
header('Cache-Control: max-age=0');

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados
$sheet->setCellValue('A1', 'producto_id');
$sheet->setCellValue('B1', 'tipo');       // entrada | salida
$sheet->setCellValue('C1', 'cantidad');
$sheet->setCellValue('D1', 'nota');

// Ejemplos
$sheet->setCellValue('A2', '1');
$sheet->setCellValue('B2', 'entrada');
$sheet->setCellValue('C2', '10');
$sheet->setCellValue('D2', 'Reposición inicial');

$sheet->setCellValue('A3', '2');
$sheet->setCellValue('B3', 'salida');
$sheet->setCellValue('C3', '5');
$sheet->setCellValue('D3', 'Venta lote A');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
