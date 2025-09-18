<?php
require 'db.php';
session_start();
require_once 'auth.php';
init_auth();
if(!isset($_SESSION['user_id'])) header("Location: login.php");
// Contar productos con poco stock
$stock_bajo = $conn->query("SELECT COUNT(*) as total FROM productos WHERE stock < 5")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pro Dashboard</title>
    <link rel="stylesheet" href="public/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="sidebar">
    <div class="logo">
    <i class="fas fa-chart-pie"></i> Pro Dashboard</div>
    <a href="index.php?page=dashboard" class="sidebar-link">
    <i class="fas fa-home"></i> Inicio    
    </a>
    <a href="index.php?page=products" class="sidebar-link">
    <i class="fas fa-box"></i> Productos
        <?php if ($stock_bajo > 0): ?>
        <span class="badge"><?= $stock_bajo ?></span>
    <?php endif; ?>
    </a>


    <a href="index.php?page=clients" class="sidebar-link">
    <i class="fas fa-users"></i> Clientes</a>
    <a href="index.php?page=ventas" class="sidebar-link">
    <i class="fas fa-shopping-cart"></i> Ventas</a>

    <!-- Submenú reportes -->
    <div class="sidebar-group">
        <button class="sidebar-toggle">
        <i class="fas fa-chart-line"></i> Reportes ▸</button>
        <div class="submenu">
            <a href="index.php?page=reportes_ventas" class="sidebar-link sub-link">
            <i class="fas fa-file-invoice-dollar"></i> Reportes de Ventas</a>
        </div>
    </div>
    <a href="index.php?page=contact" class="sidebar-link">
    <i class="fas fa-envelope"></i> Contacto</a>
    <a href="index.php?page=about" class="sidebar-link">
    <i class="fas fa-info-circle"></i> Acerca de</a>
    <a href="index.php?page=help" class="sidebar-link">
    <i class="fas fa-question-circle"></i> Ayuda</a>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <a href="index.php?page=roles" class="sidebar-link"><i class="fas fa-user-shield"></i> Roles</a>
<?php endif; ?>
</div>

<div class="main-content">
    <div class="top-bar">
        <?php
        $saludo = ($_SESSION['genero'] === 'F') ? "Bienvenida" : "Bienvenido" ;
        ?>
        <div class="user-info"><?= $saludo ?>, <?= $_SESSION['username'] ?> 😃</div>
        <a href="logout.php"><button>Logout</button></a>
    </div>

    <!-- Aquí se cargan las secciones -->
    <div id="content-area">
        <?php
        $page = $_GET['page'] ?? 'dashboard';

        switch ($page) {
            case 'dashboard':
                include 'sections/dashboard.php';
                break;
            case 'products':
                include 'sections/products.php';
                break;
            case 'clients':
                include 'sections/clients.php';
                break;
            case 'ventas':
                include 'sections/ventas.php';
                break;
            case 'reportes_ventas':
                include 'sections/reportes_ventas.php';
                break;
            case 'roles':   // 👈 nuevo caso
                include 'sections/roles.php';
                break;
            case 'contact':
                include 'sections/contact.php';
                break;
            case 'about':
                include 'sections/about.php';
                break;
            case 'help':
                include 'sections/help.php';
                break;
            default:
                include 'sections/dashboard.php';
                break;
        }
        ?>
    </div>
</div>

<script src="public/app.js"></script>
</body>
</html>
