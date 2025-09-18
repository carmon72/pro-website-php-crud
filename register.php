<?php
require 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $genero   = $_POST['genero']; 
    $role     = 'lector'; // Por defecto, cualquier registro nuevo será "lector"

    $stmt = $conn->prepare("INSERT INTO users (username, password, genero, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $password, $genero, $role);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit;
    } else {
        $error = "❌ Error al registrar usuario.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registro - Pro Dashboard</title>
<link rel="stylesheet" href="public/style.css">
<style>
body {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
  font-family: 'Segoe UI', sans-serif;

  /* 🎨 Fondo como login.php */
  background: url("public/bg-login.jpg") no-repeat center center fixed;
  background-size: cover;
}

.auth-card {
  background: rgba(255, 255, 255, 0.9);
  padding: 40px 30px 30px 30px;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.2);
  width: 100%;
  max-width: 400px;
  text-align: center;
}

.auth-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 24px rgba(0,0,0,0.15);
}

.auth-card .logo {
  font-size: 28px;
  font-weight: 700;
  color: #0b5cff;
  margin-bottom: 20px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.auth-card .logo img {
  width: 40px;
  height: 40px;
  margin-right: 10px;
}

.auth-card h2 {
  margin-bottom: 20px;
  color: #0b5cff;
}

.auth-card input, .auth-card select {
  width: 100%;
  padding: 12px;
  margin: 10px 0;
  border: 1px solid #ccc;
  border-radius: 8px;
  transition: border-color 0.3s, box-shadow 0.3s;
}

.auth-card input:focus, .auth-card select:focus {
  border-color: #0b5cff;
  box-shadow: 0 0 8px rgba(11,92,255,0.3);
  outline: none;
}

.auth-card button {
  width: 100%;
  padding: 12px;
  margin-top: 10px;
  background: #0b5cff;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.3s, transform 0.2s;
}

.auth-card button:hover {
  background: #094bb5;
  transform: translateY(-2px);
}

.auth-card p.error {
  color: red;
  text-align: center;
  margin-top: 10px;
  opacity: 0; animation: fadeIn 0.5s forwards;
}

.auth-card a {
  display: block;
  text-align: center;
  margin-top: 15px;
  color: #0b5cff;
  text-decoration: none;
  transition: color 0.3s;
}

.auth-card a:hover {
  text-decoration: underline;
  color: #094bb5;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>
</head>
<body>
<div class="auth-card">
    <div class="logo">
        <img src="public/logo.png" alt="Logo">
        <span>Pro Dashboard</span>
    </div>

    <h2>Crear Cuenta</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Usuario" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        
        <select name="genero" required>
            <option value="">Seleccione género</option>
            <option value="M">Masculino</option>
            <option value="F">Femenino</option>
        </select>
        
        <button type="submit">Registrar</button>
    </form>
    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
    <a href="login.php">Ya tengo una cuenta</a>
</div>
</body>
</html>
