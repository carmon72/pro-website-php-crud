<?php
require 'db.php';
session_start();

$token = $_GET['token'] ?? '';
$valido = false;

// Verificar token
if ($token) {
    $stmt = $conn->prepare("SELECT id, reset_expiry FROM users WHERE reset_token=?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && strtotime($user['reset_expiry']) > time()) {
        $valido = true;

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$new_pass', reset_token=NULL, reset_expiry=NULL WHERE id={$user['id']}");
            $mensaje = "✅ Contraseña actualizada correctamente. <a href='login.php'>Inicia sesión</a>";
            $valido = false; // ya se usó
        }
    } else {
        $error = "El enlace es inválido o ha expirado.";
    }
} else {
    $error = "Token no proporcionado.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Restablecer Contraseña - Pro Dashboard</title>
  <link rel="stylesheet" href="public/style.css">
  <style>
    body{
      display:flex;justify-content:center;align-items:center;height:100vh;margin:0;
      font-family:'Segoe UI',sans-serif;
      background:url("public/bg-login.jpg") no-repeat center center fixed;
      background-size:cover;
    }
    .auth-card{
      background:rgba(255,255,255,0.9);padding:40px 30px;border-radius:12px;
      box-shadow:0 6px 18px rgba(0,0,0,0.2);width:100%;max-width:420px;text-align:center;
    }
    .auth-card h2{margin-bottom:20px;color:#0b5cff;}
    .auth-card input{
      width:100%;padding:12px;margin:10px 0;border:1px solid #ccc;border-radius:8px;
    }
    .auth-card button{
      width:100%;padding:12px;margin-top:10px;background:#0b5cff;color:white;
      border:none;border-radius:8px;cursor:pointer;font-weight:600;
    }
    .auth-card button:hover{background:#094bb5;}
    .msg{margin-top:15px;padding:10px;border-radius:8px;}
    .success{background:#e6f9e6;color:#2e7d32;}
    .error{background:#fdecea;color:#c62828;}
  </style>
</head>
<body>
<div class="auth-card">
  <div class="logo">
    <img src="public/logo.png" alt="Logo" width="40" style="margin-right:8px;">
    <span>Pro Dashboard</span>
  </div>

  <?php if(isset($mensaje)): ?>
    <div class="msg success"><?= $mensaje ?></div>
  <?php elseif($valido): ?>
    <h2>Nueva Contraseña</h2>
    <form method="post">
      <input type="password" name="password" placeholder="Nueva contraseña" required>
      <button type="submit">Actualizar</button>
    </form>
  <?php else: ?>
    <div class="msg error"><?= $error ?? "Enlace inválido." ?></div>
  <?php endif; ?>
</div>
</body>
</html>
