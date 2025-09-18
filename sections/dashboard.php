<?php
//session_start();
//require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Total de usuarios
$usuarios = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

// Total de clientes
$clientes = $conn->query("SELECT COUNT(*) as total FROM clientes")->fetch_assoc()['total'];

// Total de productos
$productos = $conn->query("SELECT COUNT(*) as total FROM productos")->fetch_assoc()['total'];

// Total de ventas
$ventas = $conn->query("SELECT COUNT(*) as total FROM ventas")->fetch_assoc()['total'];

// Productos más vendidos (TOP 5)
$masVendidos = $conn->query("
SELECT p.nombre AS producto, 
SUM(vi.cantidad) AS cantidad_total,
SUM(vi.cantidad * vi.precio_unitario) AS total_vendido
FROM ventas_items vi
JOIN productos p ON vi.producto_id = p.id
GROUP BY p.id
ORDER BY cantidad_total DESC
LIMIT 5;");

// Últimas ventas (TOP 5)
$ultimasVentas = $conn->query("
-- Para últimas ventas
SELECT p.nombre AS producto, 
       c.nombre AS cliente,
       v.fecha,
       (vi.cantidad * vi.precio_unitario) AS total
FROM ventas v
JOIN ventas_items vi ON v.id = vi.venta_id
JOIN productos p ON vi.producto_id = p.id
JOIN clientes c ON v.cliente_id = c.id
ORDER BY v.fecha DESC
LIMIT 5;");
?>

<div class="dashboard-container">
    <h1>Panel de Control</h1>

    <!-- Tarjetas de estadísticas -->
    <div class="kpi-cards">
        <div class="kpi-card">👤 Usuarios <span><?= $usuarios ?></span></div>
        <div class="kpi-card">📦 Productos <span><?= $productos ?></span></div>
        <div class="kpi-card">🧑‍🤝‍🧑 Clientes <span><?= $clientes ?></span></div>
        <div class="kpi-card">💰 Ventas <span><?= $ventas ?></span></div>
    </div>

    <!-- Resumen de tablas -->
    <div class="dashboard-tables">
        <div class="table-card">
            <h2>📊 Productos más vendidos</h2>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Total vendido</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $masVendidos->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['producto'] ?></td>
                            <td><?= $row['cantidad_total'] ?></td>
                            <td>$<?= number_format($row['total_vendido'],2) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="table-card">
            <h2>🕑 Últimas ventas</h2>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $ultimasVentas->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['producto'] ?></td>
                            <td><?= $row['cliente'] ?></td>
                            <td><?= $row['fecha'] ?></td>
                            <td>$<?= number_format($row['total'], 2) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
