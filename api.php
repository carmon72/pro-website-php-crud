<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(["error"=>"No autorizado"]); exit; }

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

switch($action) {
    case 'list':
        $res = $conn->query("SELECT id, title, content, created_at, updated_at FROM items WHERE user_id=$user_id ORDER BY created_at DESC");
        $items = [];
        while($row = $res->fetch_assoc()) { $items[] = $row; }
        echo json_encode($items);
        break;

    case 'create':
        $data = json_decode(file_get_contents("php://input"), true);
        $title = $conn->real_escape_string($data['title']);
        $content = $conn->real_escape_string($data['content']);
        $conn->query("INSERT INTO items (user_id, title, content) VALUES ($user_id,'$title','$content')");
        echo json_encode(["success"=>true, "id"=>$conn->insert_id]);
        break;

    case 'update':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = (int)$data['id'];
        $title = $conn->real_escape_string($data['title']);
        $content = $conn->real_escape_string($data['content']);
        $conn->query("UPDATE items SET title='$title', content='$content' WHERE id=$id AND user_id=$user_id");
        echo json_encode(["success"=>true]);
        break;

    case 'delete':
        $id = (int)$_GET['id'];
        $conn->query("DELETE FROM items WHERE id=$id AND user_id=$user_id");
        echo json_encode(["success"=>true]);
        break;

    default:
        echo json_encode(["error"=>"Acción no válida"]);
}
?>