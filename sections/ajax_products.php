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

$user_id = (int)$_SESSION['user_id'];
$role    = $_SESSION['role'] ?? 'lector'; // 🚦 Rol del usuario
$action  = $_POST['action'] ?? null;

/** Helpers **/
function asNullIfEmpty($v) {
    if (!isset($v)) return null;
    $v = trim((string)$v);
    return ($v === '') ? null : $v;
}
function toFloat($v) {
    $v = str_replace(',', '.', (string)$v);
    return (float)$v;
}
function subirImagen($file) {
    if (empty($file['name'])) return null;
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $base = preg_replace('/[^A-Za-z0-9_\.-]/', '_', basename($file['name']));
    $fileName = time() . '_' . $base;
    $targetPath = $uploadDir . $fileName;

    if (!is_uploaded_file($file['tmp_name'])) return null;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }
    return null;
}

// 🚫 Validación de permisos: lector solo puede hacer "fetch"
if ($role === 'lector' && $action !== 'fetch') {
    echo json_encode(["error" => "No tienes permisos para realizar esta acción"]);
    exit;
}

try {
    switch ($action) {
        
/* ================= FETCH (paginación + búsqueda) ================= */
case 'fetch': {
    $page   = max(1, (int)($_POST['page'] ?? 1));
    $search = trim($_POST['search'] ?? '');
    $perPage = 5;
    $offset = ($page - 1) * $perPage;

    // Filtro de búsqueda
    $where = "WHERE 1"; // siempre verdadero
    if ($search !== '') {
        $s = $conn->real_escape_string("%$search%");
        $where .= " AND nombre LIKE '$s'";
    }

    // Total de productos
    $resTotal = $conn->query("SELECT COUNT(*) AS cnt FROM productos $where");
    $total = (int)$resTotal->fetch_assoc()['cnt'];
    $pages = max(1, ceil($total / $perPage));

    // Traer productos con límite + orden
    $res = $conn->query("SELECT * FROM productos $where ORDER BY id DESC LIMIT $offset,$perPage");
    $productos = [];
    while ($row = $res->fetch_assoc()) {
        $productos[] = $row;
    }

    echo json_encode(['success' => true, 'productos' => $productos, 'pages' => $pages]);
    exit;
}
/* ================= CREATE ================= */
case 'create': {
    $nombre        = asNullIfEmpty($_POST['nombre'] ?? '');
    $marca         = asNullIfEmpty($_POST['marca'] ?? ''); // 👈 Nuevo campo
    $categoria     = asNullIfEmpty($_POST['categoria'] ?? '');
    $precio        = toFloat($_POST['precio'] ?? 0);
    $stock         = (int)($_POST['stock'] ?? 0);
    $fecha_ingreso = asNullIfEmpty($_POST['fecha_ingreso'] ?? null);
    $descripcion   = asNullIfEmpty($_POST['descripcion'] ?? null);

    if ($nombre === null) {
        echo json_encode(['error' => 'El nombre es obligatorio']);
        exit;
    }

    $imagen = null;
    if (!empty($_FILES['imagen']['name'])) {
        $imagen = subirImagen($_FILES['imagen']);
    }

    $stmt = $conn->prepare("
        INSERT INTO productos (nombre, marca, categoria, precio, stock, fecha_ingreso, descripcion, imagen)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "sssdiiss", // 👈 8 parámetros: nombre, marca, categoria, precio, stock, fecha_ingreso, descripcion, imagen
        $nombre,
        $marca,
        $categoria,
        $precio,
        $stock,
        $fecha_ingreso,
        $descripcion,
        $imagen
    );
    $stmt->execute();
    $newId = $stmt->insert_id;

    echo json_encode([
        'success'  => true,
        'producto' => [
            'id'            => $newId,
            'nombre'        => $nombre,
            'marca'         => $marca,        // 👈 incluimos la marca en la respuesta
            'categoria'     => $categoria,
            'precio'        => $precio,
            'stock'         => $stock,
            'fecha_ingreso' => $fecha_ingreso,
            'descripcion'   => $descripcion,
            'imagen'        => $imagen
        ]
    ]);
    break;
}

       /* ================= UPDATE ================= */
/* ================= UPDATE ================= */
case 'update': {
    $id            = (int)($_POST['id'] ?? 0);
    $nombre        = asNullIfEmpty($_POST['nombre'] ?? '');
    $marca         = asNullIfEmpty($_POST['marca'] ?? ''); // 👈 Nuevo campo
    $categoria     = asNullIfEmpty($_POST['categoria'] ?? '');
    $precio        = toFloat($_POST['precio'] ?? 0);
    $stock         = (int)($_POST['stock'] ?? 0);
    $fecha_ingreso = asNullIfEmpty($_POST['fecha_ingreso'] ?? null);
    $descripcion   = asNullIfEmpty($_POST['descripcion'] ?? null);

    if ($id <= 0) { echo json_encode(['error' => 'ID inválido']); exit; }
    if ($nombre === null) { echo json_encode(['error' => 'El nombre es obligatorio']); exit; }

    // Obtener imagen actual
    $res = $conn->query("SELECT imagen FROM productos WHERE id={$id} LIMIT 1");
    if ($res->num_rows === 0) { echo json_encode(['error' => 'Producto no encontrado']); exit; }
    $row = $res->fetch_assoc();
    $oldImagen = $row['imagen'] ?? null;
    $imagen = $oldImagen;

    // Nueva imagen
    if (!empty($_FILES['imagen']['name'])) {
        if ($oldImagen && file_exists(__DIR__ . '/../uploads/' . $oldImagen)) {
            @unlink(__DIR__ . '/../uploads/' . $oldImagen);
        }
        $nueva = subirImagen($_FILES['imagen']);
        if ($nueva) $imagen = $nueva;
    }

    // 👇 Aquí está la clave: añadimos marca=? y ajustamos bind_param
    $stmt = $conn->prepare("
        UPDATE productos
        SET nombre=?, marca=?, categoria=?, precio=?, stock=?, fecha_ingreso=?, descripcion=?, imagen=?, updated_at=NOW()
        WHERE id=?
    ");
    $stmt->bind_param(
        "sssdisssi", // corregido
        $nombre,
        $marca,
        $categoria,
        $precio,
        $stock,
        $fecha_ingreso,
        $descripcion,
        $imagen,
        $id
    );

    $stmt->bind_param(
        "sssdisssi",
        $nombre,
        $marca,
        $categoria,
        $precio,
        $stock,
        $fecha_ingreso,
        $descripcion,
        $imagen,
        $id
    );

    $stmt->execute();

    echo json_encode([
        'success'  => true,
        'producto' => [
            'id'            => $id,
            'nombre'        => $nombre,
            'marca'         => $marca,
            'categoria'     => $categoria,
            'precio'        => $precio,
            'stock'         => $stock,
            'fecha_ingreso' => $fecha_ingreso,
            'descripcion'   => $descripcion,
            'imagen'        => $imagen
        ]
    ]);
    break;
}


        /* ================= DELETE ================= */
        case 'delete': {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) { echo json_encode(['error' => 'ID inválido']); exit; }

            // Obtener imagen actual
            $res = $conn->query("SELECT imagen FROM productos WHERE id={$id} LIMIT 1");
            if ($res->num_rows === 0) { 
                echo json_encode(['error' => 'Producto no encontrado']); 
                exit; 
            }
            $row = $res->fetch_assoc();
            $imagen = $row['imagen'] ?? null;

            // Eliminar producto
            $stmt = $conn->prepare("DELETE FROM productos WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            // Eliminar imagen si existe en uploads
            if ($imagen && file_exists(__DIR__ . '/../uploads/' . $imagen)) {
                @unlink(__DIR__ . '/../uploads/' . $imagen);
            }

            echo json_encode(['success' => true]);
            break;
        }

        default:
            echo json_encode(['error' => 'Acción no válida']);
    }

} catch (Throwable $e) {
    echo json_encode(['error' => 'BD/Servidor: ' . $e->getMessage()]);
}
