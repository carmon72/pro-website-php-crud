<?php
session_start();
require '../db.php';

header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$role    = $_SESSION['role'] ?? 'lector'; // 🚦 Rol del usuario
$action = $_POST['action'] ?? '';

// 🚫 Validación de permisos: Lector solo puede usar "fetch"
if ($role === 'lector' && $action !== 'fetch') {
    echo json_encode(["error" => "No tienes permisos para realizar esta acción"]);
    exit;
}

/* ================= FETCH (paginación + búsqueda) ================= */
if ($action === 'fetch') {
    $page   = max(1, (int)($_POST['page'] ?? 1));
    $search = trim($_POST['search'] ?? '');
    $perPage = 5;
    $offset = ($page - 1) * $perPage;

    // Filtro de búsqueda
    $where = "WHERE 1"; 
    if ($search !== '') {
        $s = $conn->real_escape_string("%$search%");
        $where .= " AND nombre LIKE '$s'";
    }

    // Total de clientes
    $resTotal = $conn->query("SELECT COUNT(*) AS cnt FROM clientes $where");
    $total = (int)$resTotal->fetch_assoc()['cnt'];
    $pages = max(1, ceil($total / $perPage));

    // Traer clientes con límite + orden
    $res = $conn->query("SELECT * FROM clientes $where ORDER BY id DESC LIMIT $offset,$perPage");
    $clientes = [];
    while ($row = $res->fetch_assoc()) {
        $clientes[] = $row;
    }

    echo json_encode(['success' => true, 'clientes' => $clientes, 'pages' => $pages]);
    exit;
}



/* ================= CREATE ================= */
if ($action === 'create') {
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';

    $stmt = $conn->prepare("INSERT INTO clientes (nombre, correo, telefono, direccion) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $correo, $telefono, $direccion);
    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        $row = $conn->query("SELECT * FROM clientes WHERE id=$id")->fetch_assoc();
        echo json_encode(["success" => true, "cliente" => $row]);
    } else {
        echo json_encode(["error" => "Error al guardar cliente"]);
    }
    exit;
}

/* ================= UPDATE ================= */
if ($action === 'update') {
    $id = intval($_POST['id']);
    $nombre   = $_POST['nombre'] ?? '';
    $correo   = $_POST['correo'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';

    $stmt = $conn->prepare("UPDATE clientes 
                            SET nombre=?, correo=?, telefono=?, direccion=? 
                            WHERE id=?");
    $stmt->bind_param("ssssi", $nombre, $correo, $telefono, $direccion, $id);

    if ($stmt->execute()) {
        $row = $conn->query("SELECT * FROM clientes WHERE id=$id")->fetch_assoc();
        echo json_encode(["success" => true, "cliente" => $row]);
    } else {
        echo json_encode(["error" => "Error al actualizar cliente"]);
    }
    exit;
}

/* ================= DELETE ================= */
if ($action === 'delete') {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM clientes WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Error al eliminar cliente"]);
    }
    exit;
}

echo json_encode(["error" => "Acción no válida"]);
