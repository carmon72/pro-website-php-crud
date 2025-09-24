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

$term = $_GET['q'] ?? '';
$results = [
    'clientes' => [],
    'productos' => [],
    'ventas' => []
];

if ($term !== '') {
    $like = "%" . $conn->real_escape_string($term) . "%";

    // 🔎 Buscar en clientes
    $sql = "SELECT id, nombre, correo, telefono 
            FROM clientes 
            WHERE nombre LIKE ? OR correo LIKE ? OR telefono LIKE ? 
            LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $row['link'] = "index.php?page=clients&view=" . $row['id'];
        $results['clientes'][] = $row;
    }

    // 🔎 Buscar en productos
    $sql = "SELECT id, nombre, descripcion, precio, stock 
            FROM productos 
            WHERE nombre LIKE ? OR descripcion LIKE ? 
            LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $row['link'] = "index.php?page=products&view=" . $row['id'];
        $results['productos'][] = $row;
    }

    // 🔎 Buscar en ventas (por ID de la venta o nombre del cliente)
    $sql = "SELECT v.id, v.total, v.fecha, c.nombre AS cliente
            FROM ventas v
            LEFT JOIN clientes c ON v.cliente_id = c.id
            WHERE v.id LIKE ? OR c.nombre LIKE ?
            LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $row['link'] = "index.php?page=ventas&view=" . $row['id'];
        $results['ventas'][] = $row;
    }
}

echo json_encode($results);
