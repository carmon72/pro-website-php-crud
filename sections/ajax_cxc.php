<?php
session_start();
require '../db.php';

header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "No autenticado"]);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        // === Listar cuentas por cobrar ===
        case 'fetch': {
            $sql = "
                SELECT v.id, c.nombre AS cliente, v.fecha, v.total, v.modalidad_pago, v.fecha_vencimiento,
                       IFNULL(SUM(p.monto),0) AS pagado,
                       (v.total - IFNULL(SUM(p.monto),0)) AS saldo
                FROM ventas v
                JOIN clientes c ON v.cliente_id = c.id
                LEFT JOIN ventas_pagos p ON v.id = p.venta_id
                WHERE v.modalidad_pago = 'credito'
                GROUP BY v.id
                ORDER BY v.fecha DESC
            ";
            $res = $conn->query($sql);
            $rows = [];
            while ($r = $res->fetch_assoc()) {
                $rows[] = $r;
            }
            echo json_encode($rows);
            break;
        }

        // === Registrar un abono ===
        case 'abonar': {
            $venta_id = (int)($_POST['venta_id'] ?? 0);
            $monto = (float)($_POST['monto'] ?? 0);

            if ($venta_id <= 0 || $monto <= 0) {
                echo json_encode(["error" => "Datos inválidos"]);
                exit;
            }

            // Validar que la venta exista y sea crédito
            $stmt = $conn->prepare("SELECT total, modalidad_pago FROM ventas WHERE id=? LIMIT 1");
            $stmt->bind_param("i", $venta_id);
            $stmt->execute();
            $venta = $stmt->get_result()->fetch_assoc();

            if (!$venta || $venta['modalidad_pago'] !== 'credito') {
                echo json_encode(["error" => "Venta no válida o no es a crédito"]);
                exit;
            }

            // Calcular saldo
            $res = $conn->query("SELECT IFNULL(SUM(monto),0) as pagado FROM ventas_pagos WHERE venta_id=$venta_id");
            $pagado = (float)$res->fetch_assoc()['pagado'];
            $saldo = $venta['total'] - $pagado;

            if ($monto > $saldo) {
                echo json_encode(["error" => "El abono excede el saldo pendiente"]);
                exit;
            }

            // Insertar abono
            $stmt = $conn->prepare("INSERT INTO ventas_pagos (venta_id, monto, fecha_pago) VALUES (?, ?, NOW())");
            $stmt->bind_param("id", $venta_id, $monto);
            $stmt->execute();

            echo json_encode(["success" => true]);
            break;
        }

        default:
            echo json_encode(["error" => "Acción no válida"]);
    }

} catch (Throwable $e) {
    echo json_encode(["error" => "Servidor: ".$e->getMessage()]);
}
