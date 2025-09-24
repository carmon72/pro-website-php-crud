<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cargando...</title>
<style>
body {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
  background: linear-gradient(135deg, #0b5cff, #6a11cb);
  font-family: 'Segoe UI', sans-serif;
  color: white;
  flex-direction: column;
}
.loader-text {
  font-size: 22px;
  margin-bottom: 20px;
  font-weight: bold;
}
.progress-container {
  width: 80%;
  background: rgba(255,255,255,0.2);
  border-radius: 20px;
  overflow: hidden;
}
.progress-bar {
  height: 20px;
  width: 0%;
  background: #fff;
  border-radius: 20px;
  transition: width 0.3s ease-in-out;
}
</style>
</head>
<body>
  <div class="loader-text">Cargando tu Panel...</div>
  <div class="progress-container">
    <div class="progress-bar" id="progress"></div>
  </div>

<script>
let progress = 0;
const bar = document.getElementById('progress');
const redirectUrl = localStorage.getItem('redirect') || 'index.php?page=dashboard';

let interval = setInterval(() => {
  progress += 10;
  bar.style.width = progress + '%';

  if (progress >= 100) {
    clearInterval(interval);
    window.location.href = redirectUrl;
  }
}, 180);
</script>
</body>
</html>
