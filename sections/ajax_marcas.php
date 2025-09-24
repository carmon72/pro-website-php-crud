<?php
session_start();
require '../db.php';

header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

$q = $_GET['q'] ?? '';
if ($q === '') {
    echo json_encode([]);
    exit;
}

$like = "%" . $conn->real_escape_string($q) . "%";

$stmt = $conn->prepare("SELECT nombre FROM marcas WHERE nombre LIKE ? LIMIT 10");
$stmt->bind_param("s", $like);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row['nombre'];
}
echo json_encode($rows);
