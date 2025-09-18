<?php
require 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);

    // Verificar si el usuario existe
    $stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Generar token único y guardarlo en BD
        $token = bin2hex(random_bytes(16));
        $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));
        $conn->query("UPDATE users SET reset_token='$token', reset_expiry='$expiry' WHERE id={$user['id']}");

        // En un sistema real se enviaría por email.
        // Aquí lo mostramos como link para pruebas:
        $resetLink = "http://localhost/pro-website-php-crud/reset_password.php?token=$token";
        $mensaje = "🔗 Copia este enlace en tu navegador para restablecer tu contraseña: <br><a href='$resetLink'>$resetLink</a>";
    } else {
        $error = "El usuario no existe.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Olvidé mi contraseña - Pro Dashboard</title>
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

    .auth-card .logo{
    font-size:28px;
    font-weight:700;
    color:#0b5cff;
    margin-bottom:20px;
    display:flex;
    justify-content:center;
    align-items:center;
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
    .auth-card a{display:block;margin-top:15px;color:#0b5cff;text-decoration:none;}
    .auth-card a:hover{text-decoration:underline;}
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
  <h2>Recuperar Contraseña</h2>
  <form method="post">
    <input type="text" name="username" placeholder="Usuario" required>
    <button type="submit">Enviar enlace</button>
  </form>
  <a href="login.php">🔙 Volver al login</a>
  <?php if(isset($mensaje)) echo "<div class='msg success'>$mensaje</div>"; ?>
  <?php if(isset($error)) echo "<div class='msg error'>$error</div>"; ?>
</div>
</body>
</html>
