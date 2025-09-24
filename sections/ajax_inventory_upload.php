<?php
session_start();
require '../db.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $usuario_id = $_SESSION['user_id'] ?? 0;

    try {
        $fileTmp = $_FILES['file']['tmp_name'];
        $spreadsheet = IOFactory::load($fileTmp);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Se espera encabezado en la primera fila: producto_id | tipo | cantidad | nota
        $insertados = 0;
        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Saltar encabezado

            $producto_id = intval($row[0] ?? 0);
            $tipo        = strtolower(trim($row[1] ?? ''));
            $cantidad    = intval($row[2] ?? 0);
            $nota        = $row[3] ?? '';

            if ($producto_id <= 0 || !in_array($tipo, ['entrada','salida']) || $cantidad <= 0) {
                continue; // Saltar fila inválida
            }

            // Verificar producto
            $stmt = $conn->prepare("SELECT stock FROM productos WHERE id = ?");
            $stmt->bind_param("i", $producto_id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 0) continue;
            $producto = $res->fetch_assoc();
            $stock_actual = (int)$producto['stock'];

            if ($tipo === 'salida' && $stock_actual < $cantidad) continue;

            // Insertar movimiento
            $stmt = $conn->prepare("INSERT INTO inventario_movimientos 
                (producto_id, tipo, cantidad, usuario_id, nota) 
                VALUES (?,?,?,?,?)");
            $stmt->bind_param("isiis", $producto_id, $tipo, $cantidad, $usuario_id, $nota);
            $stmt->execute();

            // Actualizar stock
            if ($tipo === 'entrada') {
                $conn->query("UPDATE productos SET stock = stock + $cantidad WHERE id = $producto_id");
            } else {
                $conn->query("UPDATE productos SET stock = stock - $cantidad WHERE id = $producto_id");
            }

            $insertados++;
        }

        echo json_encode(['success' => true, 'insertados' => $insertados]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error al procesar Excel: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['error' => 'Archivo no válido']);
