<?php
require 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Traemos también el campo genero y rol
    $sql = "SELECT id, username, password, genero, role FROM users WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['genero']   = $user['genero'];
        $_SESSION['role']     = $user['role'];
        require_once 'auth.php';
        init_auth();
        header("Location: index.php?page=dashboard");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Pro Dashboard</title>
<link rel="stylesheet" href="public/style.css">

<!-- 🎨 FontAwesome para íconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body{
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
  font-family: 'Segoe UI', sans-serif;
  
  /* Fondo bonito para tu novia 💕 */
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
.auth-card:hover{
  transform: translateY(-5px);
  box-shadow:0 12px 24px rgba(0,0,0,0.15);
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
.auth-card .logo img{
  width:40px;
  height:40px;
  margin-right:10px;
}
.auth-card h2{margin-bottom:20px;color:#0b5cff;}
.auth-card input{
  width:100%;padding:12px;margin:10px 0;border:1px solid #ccc;border-radius:8px;
  transition: border-color 0.3s, box-shadow 0.3s;
}
.auth-card input:focus{
  border-color:#0b5cff;
  box-shadow:0 0 8px rgba(11,92,255,0.3);
  outline:none;
}
.auth-card button{
  width:100%;padding:12px;margin-top:10px;background:#0b5cff;color:white;
  border:none;border-radius:8px;cursor:pointer;font-weight:600;
  transition: background 0.3s, transform 0.2s;
}
.auth-card button:hover{
  background:#094bb5;
  transform: translateY(-2px);
}
.auth-card p.error{
  color:red;text-align:center;margin-top:10px;
  opacity:0; animation:fadeIn 0.5s forwards;
}
.auth-card a{
  display:block;text-align:center;margin-top:15px;color:#0b5cff;text-decoration:none;
  transition: color 0.3s;
}
.auth-card a:hover{text-decoration:underline; color:#094bb5;}
@keyframes fadeIn{ from{opacity:0;} to{opacity:1;} }

/* 🔑 Estilos para el ojito */
.password-wrapper {
  position: relative;
  width: 100%;
}
#togglePassword {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  font-size: 18px;
  color: #666;
}
</style>
</head>
<body>
<div class="auth-card">
    <!-- Logo/Header Minimalista -->
    <div class="logo">
        <img src="public/logo.png" alt="Logo">
        <span>Pro Dashboard</span>
    </div>

    <h2>Iniciar Sesión</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Usuario" required>

        <!-- Campo de contraseña con ojito -->
        <div class="password-wrapper">
            <input type="password" id="password" name="password" placeholder="Contraseña" required>
            <i class="fa-solid fa-eye" id="togglePassword"></i>
        </div>

        <button type="submit">Entrar</button>
    </form>
    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
    <a href="register.php">Crear una cuenta</a>
    <a href="forgot_password.php">¿Olvidaste tu contraseña?</a>
</div>

<script>
const togglePassword = document.getElementById("togglePassword");
const passwordInput = document.getElementById("password");

togglePassword.addEventListener("click", () => {
    const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
    passwordInput.setAttribute("type", type);

    // Cambiar ícono
    if (type === "password") {
        togglePassword.classList.remove("fa-eye-slash");
        togglePassword.classList.add("fa-eye");
    } else {
        togglePassword.classList.remove("fa-eye");
        togglePassword.classList.add("fa-eye-slash");
    }
});
</script>
</body>
</html>
