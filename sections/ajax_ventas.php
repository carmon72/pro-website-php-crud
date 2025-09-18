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

$action = $_POST['action'] ?? null;

// Si viene JSON (por ejemplo en create)
if (empty($action)) {
    $input = json_decode(file_get_contents("php://input"), true);
    if ($input && isset($input['action'])) {
        $action = $input['action'];
    }
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

            $resP = $conn->query("SELECT id, nombre, precio FROM productos ORDER BY nombre ASC");
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

            if ($cliente_id <= 0) {
                echo json_encode(['error' => 'Cliente no válido']);
                exit;
            }
            if (empty($items)) {
                echo json_encode(['error' => 'El carrito está vacío']);
                exit;
            }

            $conn->begin_transaction();

            // Insertar venta (sin user_id)
            $stmt = $conn->prepare("INSERT INTO ventas (cliente_id, fecha, total) VALUES (?, NOW(), 0)");
            $stmt->bind_param("i", $cliente_id);
            $stmt->execute();
            $venta_id = $stmt->insert_id;

            $total = 0;
            $stmtItem = $conn->prepare("INSERT INTO ventas_items (venta_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
            foreach ($items as $it) {
                $pid = (int)$it['id'];
                $cant = (int)$it['cantidad'];
                $precio = (float)$it['precio'];
                $subtotal = $cant * $precio;
                $total += $subtotal;

                $stmtItem->bind_param("iiid", $venta_id, $pid, $cant, $precio);
                $stmtItem->execute();

                // Reducir stock del producto
                $conn->query("UPDATE productos SET stock = stock - $cant WHERE id=$pid");
            }

            // Actualizar total de la venta
            $stmtUpd = $conn->prepare("UPDATE ventas SET total=? WHERE id=?");
            $stmtUpd->bind_param("di", $total, $venta_id);
            $stmtUpd->execute();

            $conn->commit();

            echo json_encode(['success' => true, 'venta_id' => $venta_id]);
            break;
        }

        /* ================== Historial de ventas ================== */
        case 'fetch': {
            $page   = max(1, (int)($_POST['page'] ?? 1));
            $perPage = 5;
            $offset = ($page - 1) * $perPage;

            $resTotal = $conn->query("SELECT COUNT(*) AS cnt FROM ventas");
            $total = (int)$resTotal->fetch_assoc()['cnt'];
            $pages = max(1, ceil($total / $perPage));

            $sql = "
                SELECT v.id, c.nombre AS cliente, v.fecha, v.total
                FROM ventas v
                JOIN clientes c ON v.cliente_id = c.id
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
