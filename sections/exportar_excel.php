<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include __DIR__ . '/../db.php';

if (!$conn) {
    die("Error: no hay conexión con la base de datos.");
}

// Consulta ajustada: calculamos subtotal en vez de traerlo directo
$sql = "SELECT vi.id,
               p.nombre AS producto,
               c.nombre AS cliente,
               vi.cantidad,
               (vi.cantidad * vi.precio_unitario) AS subtotal,
               v.fecha
        FROM ventas_items vi
        INNER JOIN productos p ON vi.producto_id = p.id
        INNER JOIN ventas v ON vi.venta_id = v.id
        INNER JOIN clientes c ON v.cliente_id = c.id";

$query = mysqli_query($conn, $sql);

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Producto');
$sheet->setCellValue('C1', 'Cliente');
$sheet->setCellValue('D1', 'Cantidad');
$sheet->setCellValue('E1', 'Subtotal');
$sheet->setCellValue('F1', 'Fecha');

// Llenar filas
$fila = 2;
while ($row = mysqli_fetch_assoc($query)) {
    $sheet->setCellValue('A' . $fila, $row['id']);
    $sheet->setCellValue('B' . $fila, $row['producto']);
    $sheet->setCellValue('C' . $fila, $row['cliente']);
    $sheet->setCellValue('D' . $fila, $row['cantidad']);
    $sheet->setCellValue('E' . $fila, number_format($row['subtotal'], 2));
    $sheet->setCellValue('F' . $fila, $row['fecha']);
    $fila++;
}

// Ajustar ancho automático
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Descargar archivo
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="reporte_ventas.xlsx"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
exit;
