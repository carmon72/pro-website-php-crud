<?php
session_start();
require '../db.php';

header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$role   = $_SESSION['role'] ?? 'lector'; 
$action = $_POST['action'] ?? null;

// Si viene JSON
if (empty($action)) {
    $input = json_decode(file_get_contents("php://input"), true);
    if ($input && isset($input['action'])) {
        $action = $input['action'];
    }
}

if ($role === 'lector' && $action !== 'fetch') {
    echo json_encode(["error" => "No tienes permisos para realizar esta acción"]);
    exit;
}

try {
    switch ($action) {

        /* ================== Cargar combos ================== */
        case 'loadCombos': {
            $clientes = [];
            $productos = [];

            $resC = $conn->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC");
            while ($row = $resC->fetch_assoc()) {
                $clientes[] = $row;
            }

            $resP = $conn->query("SELECT id, nombre, marca, precio, stock FROM productos ORDER BY nombre ASC");
            while ($row = $resP->fetch_assoc()) {
                $productos[] = $row;
            }

            echo json_encode(['clientes' => $clientes, 'productos' => $productos]);
            break;
        }

       /* ================== Crear nueva venta ================== */
case 'create': {
    $cliente_id = (int)($input['cliente_id'] ?? 0);
    $items = $input['items'] ?? [];
    $modalidad = $input['modalidad_pago'] ?? 'contado';
    $abono = (float)($input['abono'] ?? 0);

    // 👇 Solo asignamos fecha de vencimiento si es crédito
    $fecha_vencimiento = null;
    if ($modalidad === 'credito' && !empty($input['fecha_vencimiento'])) {
        $fecha_vencimiento = $input['fecha_vencimiento'];
    }

    if ($cliente_id <= 0) {
        echo json_encode(['error' => 'Cliente no válido']); exit;
    }
    if (empty($items)) {
        echo json_encode(['error' => 'El carrito está vacío']); exit;
    }

    $conn->begin_transaction();

    // Calcular total
    $total = 0;
    foreach ($items as $it) {
        $cant = (int)$it['cantidad'];
        $precio = (float)$it['precio'];
        $total += $cant * $precio;
    }

    // Insertar venta
    $stmt = $conn->prepare("
        INSERT INTO ventas (cliente_id, fecha, total, modalidad_pago, fecha_vencimiento) 
        VALUES (?, NOW(), ?, ?, ?)
    ");
    $stmt->bind_param("idss", $cliente_id, $total, $modalidad, $fecha_vencimiento);
    $stmt->execute();
    $venta_id = $stmt->insert_id;

    // Insertar items
    $stmtItem = $conn->prepare("INSERT INTO ventas_items (venta_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
    foreach ($items as $it) {
        $pid = (int)$it['id'];
        $cant = (int)$it['cantidad'];
        $precio = (float)$it['precio'];

        $stmtItem->bind_param("iiid", $venta_id, $pid, $cant, $precio);
        $stmtItem->execute();

        // Reducir stock
        $conn->query("UPDATE productos SET stock = stock - $cant WHERE id=$pid");
    }

    // Insertar pagos
    if ($modalidad === 'contado') {
        $stmtPago = $conn->prepare("INSERT INTO ventas_pagos (venta_id, monto, fecha_pago) VALUES (?, ?, NOW())");
        $stmtPago->bind_param("id", $venta_id, $total);
        $stmtPago->execute();
    } elseif ($modalidad === 'credito' && $abono > 0) {
        $stmtPago = $conn->prepare("INSERT INTO ventas_pagos (venta_id, monto, fecha_pago) VALUES (?, ?, NOW())");
        $stmtPago->bind_param("id", $venta_id, $abono);
        $stmtPago->execute();
    }

    $conn->commit();

    echo json_encode(['success' => true, 'venta_id' => $venta_id]);
    break;
}

        /* ================== Historial de ventas ================== */
        case 'fetch': {
            $page   = max(1, (int)($_POST['page'] ?? 1));
            $perPage = 5;
            $offset = ($page - 1) * $perPage;

            // Total de ventas para la paginación
            $resTotal = $conn->query("SELECT COUNT(*) AS cnt FROM ventas");
            $total = (int)$resTotal->fetch_assoc()['cnt'];
            $pages = max(1, ceil($total / $perPage));

            // Consulta principal con marcas
            $sql = "
                SELECT v.id, c.nombre AS cliente, v.fecha, v.total, v.modalidad_pago, v.fecha_vencimiento,
                       GROUP_CONCAT(DISTINCT pr.marca SEPARATOR ', ') AS marcas, -- 👈 concatenamos marcas
                       IFNULL(SUM(pg.monto),0) AS pagado,
                       (v.total - IFNULL(SUM(pg.monto),0)) AS saldo
                FROM ventas v
                JOIN clientes c ON v.cliente_id = c.id
                JOIN ventas_items vi ON vi.venta_id = v.id
                JOIN productos pr ON vi.producto_id = pr.id
                LEFT JOIN ventas_pagos pg ON v.id = pg.venta_id
                GROUP BY v.id
                ORDER BY v.id DESC
                LIMIT $offset, $perPage
            ";
            $res = $conn->query($sql);

            $ventas = [];
            while ($row = $res->fetch_assoc()) {
                $ventas[] = $row;
            }

            echo json_encode(['ventas' => $ventas, 'pages' => $pages]);
            break;
        }

        default:
            echo json_encode(['error' => 'Acción no válida']);
    }

} catch (Throwable $e) {
    if ($conn->errno) $conn->rollback();
    echo json_encode(['error' => 'BD/Servidor: ' . $e->getMessage()]);
}
