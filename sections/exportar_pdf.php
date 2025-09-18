<?php
require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;

include __DIR__ . '/../db.php';

if (!$conn) {
    die("Error: no hay conexión con la base de datos.");
}

// Consulta ajustada: calcular subtotal en vez de traerlo directo
$sql = "SELECT vi.id,
               p.nombre AS producto,
               c.nombre AS cliente,
               vi.cantidad,
               (vi.precio_unitario * vi.cantidad) AS subtotal,
               v.fecha
        FROM ventas_items vi
        INNER JOIN productos p ON vi.producto_id = p.id
        INNER JOIN ventas v ON vi.venta_id = v.id
        INNER JOIN clientes c ON v.cliente_id = c.id";

$query = mysqli_query($conn, $sql);

// Generar HTML
$html = '
    <h2 style="text-align:center;">Reporte de Ventas</h2>
    <table border="1" cellspacing="0" cellpadding="5" width="100%">
        <thead>
            <tr style="background-color:#f2f2f2; text-align:center;">
                <th>ID</th>
                <th>Producto</th>
                <th>Cliente</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
';

while ($row = mysqli_fetch_assoc($query)) {
    $subtotal = "$" . number_format($row['subtotal'], 2); // ✅ formateado
    $html .= "<tr>
                <td>{$row['id']}</td>
                <td>{$row['producto']}</td>
                <td>{$row['cliente']}</td>
                <td>{$row['cantidad']}</td>
                <td style='text-align:right;'>{$subtotal}</td>
                <td>{$row['fecha']}</td>
              </tr>";
}

$html .= '
        </tbody>
    </table>
';

// Crear PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("reporte_ventas.pdf", array("Attachment" => true));
exit;
