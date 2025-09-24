<?php
session_start();
require '../db.php';

header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

$action = $_POST['action'] ?? '';

if ($action === 'addMovement') {
    $producto_id = intval($_POST['producto_id'] ?? 0);
    $tipo        = $_POST['tipo'] ?? '';
    $cantidad    = intval($_POST['cantidad'] ?? 0);
    $marca       = trim($_POST['marca'] ?? null); // 👈 ahora usamos marca
    $usuario_id  = $_SESSION['user_id'] ?? 0;

    if ($producto_id <= 0 || $cantidad <= 0 || !in_array($tipo, ['entrada','salida'])) {
        echo json_encode(['error' => 'Datos inválidos.']);
        exit;
    }

    // Verificar que el producto exista
    $stmt = $conn->prepare("SELECT stock FROM productos WHERE id = ?");
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        echo json_encode(['error' => 'Producto no encontrado.']);
        exit;
    }
    $producto = $res->fetch_assoc();
    $stock_actual = (int)$producto['stock'];

    // Validar stock en caso de salida
    if ($tipo === 'salida' && $stock_actual < $cantidad) {
        echo json_encode(['error' => 'Stock insuficiente para salida.']);
        exit;
    }

    // 1) Insertar movimiento (fecha se autogenera con CURRENT_TIMESTAMP)
    $stmt = $conn->prepare("
        INSERT INTO inventario_movimientos (producto_id, tipo, cantidad, usuario_id, marca) 
        VALUES (?,?,?,?,?)
    ");
    $stmt->bind_param("isiis", $producto_id, $tipo, $cantidad, $usuario_id, $marca);
    $stmt->execute();

    // 2) Actualizar stock
    if ($tipo === 'entrada') {
        $stmt = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
    } else {
        $stmt = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
    }
    $stmt->bind_param("ii", $cantidad, $producto_id);
    $stmt->execute();

    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'fetchMovements') {
    $result = $conn->query("
        SELECT m.id, p.nombre AS producto, m.tipo, m.cantidad, m.fecha, m.marca, u.username AS usuario
        FROM inventario_movimientos m
        JOIN productos p ON m.producto_id = p.id
        JOIN users u ON m.usuario_id = u.id
        ORDER BY m.fecha DESC
        LIMIT 50
    ");

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    echo json_encode(['movimientos' => $rows]);
    exit;
}

echo json_encode(['error' => 'Acción no válida']);
